<?php

namespace App\Services;

use App\Models\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Docs: https://www.mercadopago.com/developers/es/reference
 * Requiere en .env: MP_ACCESS_TOKEN
 */
class MercadoPagoService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.mercadopago.com/',
            'timeout' => 20,
            'headers' => [
                'Authorization' => 'Bearer '.config('services.mercadopago.access_token'),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Crea una preferencia de pago y devuelve la URL de checkout a la que redirigir al cliente.
     */
    public function createPreference(Order $order): ?string
    {
        $items = $order->items->map(fn ($item) => [
            'title' => $item->product_name,
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'currency_id' => $order->currency,
        ])->all();

        try {
            $response = $this->client->post('checkout/preferences', [
                'json' => [
                    'items' => $items,
                    'payer' => ['name' => $order->customer_name, 'email' => $order->customer_email],
                    'external_reference' => $order->order_number,
                    'back_urls' => [
                        'success' => route('shop.order.confirmation', $order->order_number),
                        'failure' => route('shop.checkout'),
                        'pending' => route('shop.order.confirmation', $order->order_number),
                    ],
                    'auto_return' => 'approved',
                    'notification_url' => route('webhooks.mercadopago'),
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            $order->update(['mp_preference_id' => $data['id'] ?? null]);

            return $data['init_point'] ?? $data['sandbox_init_point'] ?? null;
        } catch (GuzzleException $e) {
            Log::error("Mercado Pago: fallo al crear preferencia para orden {$order->order_number}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Consulta el estado real de un pago (nunca confiar solo en el query string del webhook).
     */
    public function getPayment(string $paymentId): ?array
    {
        try {
            $response = $this->client->get("v1/payments/{$paymentId}");

            return json_decode((string) $response->getBody(), true);
        } catch (GuzzleException $e) {
            Log::error("Mercado Pago: fallo al consultar pago {$paymentId}: {$e->getMessage()}");
            return null;
        }
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use App\Services\Concerns\AuthenticatesWithCj;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Envía el pedido a CJ Dropshipping para que lo despache al cliente final.
 * Docs: https://developers.cjdropshipping.com/
 *
 * ADVERTENCIA: a diferencia de CjTrendingService (que sí probamos contra la
 * cuenta real y funciona), este endpoint de creación de orden NO fue
 * verificado en vivo — el nombre exacto del endpoint y los campos requeridos
 * varían según el plan de API de tu cuenta CJ. Antes de confiarle pedidos
 * reales, hacé una prueba controlada (un pedido de bajo valor) y revisá
 * storage/logs/laravel.log para confirmar la respuesta real de la API.
 */
class CjOrderService
{
    use AuthenticatesWithCj;

    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.cj_dropshipping.base_uri', 'https://developers.cjdropshipping.com/api2.0/v1/'),
            'timeout' => 30,
        ]);
    }

    public function submitOrder(Order $order): ?string
    {
        $token = $this->authenticate($this->client);

        if (! $token) {
            return null;
        }

        $address = $order->shipping_address;

        try {
            $response = $this->client->post('shopping/order/createOrderV2', [
                'headers' => ['CJ-Access-Token' => $token],
                'json' => [
                    'orderNumber' => $order->order_number,
                    'shippingCustomerName' => $order->customer_name,
                    'shippingPhone' => $order->customer_phone,
                    'shippingCountryCode' => $address['country_code'] ?? '',
                    'shippingProvince' => $address['region'] ?? '',
                    'shippingCity' => $address['city'] ?? '',
                    'shippingAddress' => $address['street'] ?? '',
                    'shippingZip' => $address['postal_code'] ?? '',
                    'products' => $order->items->map(fn ($item) => [
                        'vid' => $item->supplier_product_id,
                        'quantity' => $item->quantity,
                    ])->all(),
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            if (! ($data['result'] ?? false)) {
                Log::error("CJ Order: la API rechazó la orden {$order->order_number}: ".json_encode($data));
                return null;
            }

            return $data['data']['orderId'] ?? $data['data']['orderNum'] ?? null;
        } catch (GuzzleException $e) {
            Log::error("CJ Order: fallo al enviar orden {$order->order_number}: {$e->getMessage()}");
            return null;
        }
    }
}

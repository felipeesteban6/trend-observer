<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SubmitOrderToCjJob;
use App\Models\Order;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request, MercadoPagoService $mercadoPago): Response
    {
        $type = $request->query('type') ?? $request->query('topic');
        $paymentId = $request->query('data.id') ?? $request->query('id');

        if ($type !== 'payment' || ! $paymentId) {
            return response()->noContent();
        }

        // Nunca confiar en el payload del webhook solo: se re-consulta el pago
        // real contra la API de Mercado Pago para confirmar estado y monto.
        $payment = $mercadoPago->getPayment($paymentId);

        if (! $payment) {
            return response()->noContent();
        }

        $orderNumber = $payment['external_reference'] ?? null;
        $order = $orderNumber ? Order::where('order_number', $orderNumber)->first() : null;

        if (! $order) {
            Log::warning("Mercado Pago webhook: orden no encontrada para external_reference={$orderNumber}");
            return response()->noContent();
        }

        if ($order->status !== Order::STATUS_PENDING_PAYMENT) {
            return response()->noContent();
        }

        $status = $payment['status'] ?? null;

        if ($status === 'approved') {
            $order->update(['status' => Order::STATUS_PAID, 'mp_payment_id' => (string) $paymentId]);
            SubmitOrderToCjJob::dispatch($order);
        } elseif (in_array($status, ['rejected', 'cancelled'], true)) {
            $order->update(['status' => Order::STATUS_PAYMENT_FAILED, 'mp_payment_id' => (string) $paymentId]);
        }

        return response()->noContent();
    }
}

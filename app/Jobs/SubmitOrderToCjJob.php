<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\CjOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SubmitOrderToCjJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 300;

    public function __construct(private readonly Order $order)
    {
    }

    public function handle(CjOrderService $service): void
    {
        if ($this->order->status !== Order::STATUS_PAID) {
            return;
        }

        if (! config('services.cj_dropshipping.auto_submit_orders')) {
            Log::info("CJ Order: envío automático desactivado (CJ_AUTO_SUBMIT_ORDERS=false). Orden {$this->order->order_number} pagada, pendiente de envío manual.");
            return;
        }

        $cjOrderId = $service->submitOrder($this->order);

        if ($cjOrderId) {
            $this->order->update([
                'status' => Order::STATUS_SUBMITTED_TO_SUPPLIER,
                'cj_order_id' => $cjOrderId,
            ]);
        } else {
            $this->order->update(['status' => Order::STATUS_SUBMISSION_FAILED]);
        }
    }
}

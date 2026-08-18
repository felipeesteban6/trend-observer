<?php

namespace App\Jobs;

use App\Models\SupplierTrendingProduct;
use App\Services\CjTrendingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchSupplierTrendingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 120;

    public function handle(CjTrendingService $service): void
    {
        $items = $service->fetchTrending();

        if (empty($items)) {
            Log::warning('Sin datos trending de CJ Dropshipping en esta corrida.');
            return;
        }

        $today = now()->toDateString();

        foreach ($items as $item) {
            SupplierTrendingProduct::updateOrCreate(
                [
                    'supplier' => 'cj_dropshipping',
                    'supplier_product_id' => $item['supplier_product_id'],
                    'captured_on' => $today,
                ],
                [
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'price' => $item['price'],
                    'sales_count' => $item['sales_count'],
                    'rank' => $item['rank'],
                    'image_url' => $item['image_url'],
                    'product_url' => $item['product_url'],
                ]
            );
        }
    }
}

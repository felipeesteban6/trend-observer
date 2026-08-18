<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\SupplierTrendingProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncProductsFromTrending extends Command
{
    protected $signature = 'products:sync-from-trending
        {--publish : Publica los productos creados/actualizados en el catálogo}';

    protected $description = 'Crea/actualiza productos del catálogo a partir de lo último capturado de CJ Dropshipping';

    public function handle(): int
    {
        $latestDate = SupplierTrendingProduct::max('captured_on');

        if (! $latestDate) {
            $this->error('No hay productos trending capturados todavía. Corré primero FetchSupplierTrendingJob.');
            return self::FAILURE;
        }

        $markup = (float) config('shop.markup_multiplier');
        $items = SupplierTrendingProduct::where('captured_on', $latestDate)->get();

        $count = 0;

        foreach ($items as $item) {
            Product::updateOrCreate(
                ['supplier_trending_product_id' => $item->id],
                [
                    'name' => $item->name,
                    'slug' => Str::slug($item->name).'-'.$item->supplier_product_id,
                    'image_url' => $item->image_url,
                    'category' => $item->category,
                    'cost_price' => $item->price,
                    'sale_price' => round($item->price * $markup, 2),
                    'currency' => 'USD',
                    'is_published' => $this->option('publish'),
                ]
            );
            $count++;
        }

        $this->info("{$count} productos sincronizados desde el snapshot del {$latestDate}.");

        return self::SUCCESS;
    }
}

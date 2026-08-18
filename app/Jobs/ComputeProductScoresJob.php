<?php

namespace App\Jobs;

use App\Models\ProductScore;
use App\Models\SearchKeyword;
use App\Models\SupplierTrendingProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Combina la señal de Google Trends (crecimiento de interés) con la señal del
 * proveedor (rank / ventas) en un score único por día, que es lo que
 * finalmente se muestra ordenado en el dashboard.
 *
 * Pesos por defecto: 60% tendencia de búsqueda, 40% señal del proveedor.
 * Se pueden ajustar en config/scoring.php sin tocar este job.
 */
class ComputeProductScoresJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $today = now()->toDateString();
        $trendWeight = config('scoring.trend_weight', 0.6);
        $supplierWeight = config('scoring.supplier_weight', 0.4);

        SearchKeyword::where('is_active', true)->get()->each(function (SearchKeyword $keyword) use ($today, $trendWeight, $supplierWeight) {
            $growth = $keyword->growthScore();
            // Normalizamos crecimiento (puede ser negativo o >100) a 0-100.
            $normalizedTrend = max(0, min(100, $growth));

            ProductScore::updateOrCreate(
                ['search_keyword_id' => $keyword->id, 'supplier_trending_product_id' => null, 'computed_on' => $today],
                [
                    'label' => $keyword->term,
                    'trend_growth_score' => $normalizedTrend,
                    'supplier_signal_score' => 0,
                    'total_score' => round($normalizedTrend * $trendWeight, 2),
                ]
            );
        });

        SupplierTrendingProduct::where('captured_on', $today)->get()->each(function (SupplierTrendingProduct $product) use ($today, $trendWeight, $supplierWeight) {
            $supplierScore = $product->supplierSignalScore();

            ProductScore::updateOrCreate(
                ['search_keyword_id' => null, 'supplier_trending_product_id' => $product->id, 'computed_on' => $today],
                [
                    'label' => $product->name,
                    'trend_growth_score' => 0,
                    'supplier_signal_score' => $supplierScore,
                    'total_score' => round($supplierScore * $supplierWeight, 2),
                ]
            );
        });
    }
}

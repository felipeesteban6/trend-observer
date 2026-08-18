<?php

use App\Jobs\ComputeProductScoresJob;
use App\Jobs\FetchGoogleTrendsJob;
use App\Jobs\FetchSupplierTrendingJob;
use App\Models\SearchKeyword;
use Illuminate\Support\Facades\Schedule;

// Trae tendencias de búsqueda para cada keyword activa. Se espacian con
// jitter para no golpear el endpoint no oficial de Google Trends todas al
// mismo segundo (menor riesgo de bloqueo temporal).
Schedule::call(function () {
    SearchKeyword::where('is_active', true)->get()->each(function (SearchKeyword $keyword, int $i) {
        FetchGoogleTrendsJob::dispatch($keyword)->delay(now()->addSeconds($i * 15));
    });
})->dailyAt('03:00')->name('fetch-google-trends')->withoutOverlapping();

// Trae el listado trending/best-sellers del proveedor.
Schedule::job(new FetchSupplierTrendingJob)->dailyAt('04:00')->name('fetch-supplier-trending');

// Recalcula el ranking combinado una vez que ya llegaron los datos del día.
Schedule::job(new ComputeProductScoresJob)->dailyAt('05:00')->name('compute-product-scores');

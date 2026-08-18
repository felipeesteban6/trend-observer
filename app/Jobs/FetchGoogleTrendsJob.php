<?php

namespace App\Jobs;

use App\Models\SearchKeyword;
use App\Models\TrendSnapshot;
use App\Services\GoogleTrendsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchGoogleTrendsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 300; // 5 min, para no insistir agresivo contra Google

    public function __construct(private readonly SearchKeyword $keyword)
    {
    }

    public function handle(GoogleTrendsService $service): void
    {
        $points = $service->dailyInterest($this->keyword->term, $this->keyword->language, $this->keyword->geo);

        if (empty($points)) {
            Log::warning("Sin datos de Google Trends para '{$this->keyword->term}' (puede ser rate limit).");
            return;
        }

        foreach ($points as $point) {
            TrendSnapshot::updateOrCreate(
                ['search_keyword_id' => $this->keyword->id, 'date' => $point['date']],
                ['interest' => $point['interest']]
            );
        }
    }
}

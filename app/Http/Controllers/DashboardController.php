<?php

namespace App\Http\Controllers;

use App\Models\ProductScore;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $date = $request->date('date')?->toDateString() ?? ProductScore::max('computed_on');

        $ranking = ProductScore::query()
            ->with(['keyword', 'supplierProduct'])
            ->when($date, fn ($q) => $q->where('computed_on', $date))
            ->orderByDesc('total_score')
            ->limit(50)
            ->get();

        return Inertia::render('Dashboard', [
            'ranking' => $ranking,
            'date' => $date,
            'availableDates' => ProductScore::query()->distinct()->orderByDesc('computed_on')->pluck('computed_on'),
        ]);
    }
}

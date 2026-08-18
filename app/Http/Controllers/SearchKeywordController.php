<?php

namespace App\Http\Controllers;

use App\Models\SearchKeyword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchKeywordController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Keywords/Index', [
            'keywords' => SearchKeyword::withCount('snapshots')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'term' => ['required', 'string', 'max:255'],
            'language' => ['required', 'in:es,en'],
            'geo' => ['nullable', 'string', 'max:5'],
            'category' => ['nullable', 'string', 'max:120'],
        ]);

        SearchKeyword::create($validated);

        return back()->with('success', 'Keyword agregada. Se empezará a monitorear en la próxima corrida programada.');
    }

    public function update(Request $request, SearchKeyword $keyword): RedirectResponse
    {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);
        $keyword->update($validated);

        return back();
    }

    public function destroy(SearchKeyword $keyword): RedirectResponse
    {
        $keyword->delete();

        return back()->with('success', 'Keyword eliminada.');
    }
}

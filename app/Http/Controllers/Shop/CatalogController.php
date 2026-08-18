<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Shop/Index', [
            'products' => Product::query()
                ->where('is_published', true)
                ->latest()
                ->get(['id', 'name', 'slug', 'image_url', 'category', 'sale_price', 'currency']),
        ]);
    }

    public function show(Product $product): Response
    {
        abort_unless($product->is_published, 404);

        return Inertia::render('Shop/Show', [
            'product' => $product,
        ]);
    }
}

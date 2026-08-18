<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(CartService $cart): Response
    {
        return Inertia::render('Shop/Cart', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
        ]);
    }

    public function store(Request $request, CartService $cart): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $product = Product::where('is_published', true)->findOrFail($validated['product_id']);
        $cart->add($product, $validated['quantity'] ?? 1);

        return back()->with('success', 'Producto agregado al carrito.');
    }

    public function update(Request $request, int $product, CartService $cart): RedirectResponse
    {
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:20']]);
        $cart->update($product, $validated['quantity']);

        return back();
    }

    public function destroy(int $product, CartService $cart): RedirectResponse
    {
        $cart->remove($product);

        return back();
    }
}

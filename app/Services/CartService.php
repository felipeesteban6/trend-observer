<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Carrito basado en sesión — no requiere login. Guarda [product_id => quantity].
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    public function add(Product $product, int $quantity = 1): void
    {
        $cart = $this->raw();
        $cart[$product->id] = ($cart[$product->id] ?? 0) + max(1, $quantity);
        Session::put(self::SESSION_KEY, $cart);
    }

    public function update(int $productId, int $quantity): void
    {
        $cart = $this->raw();

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }

        Session::put(self::SESSION_KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->raw();
        unset($cart[$productId]);
        Session::put(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function raw(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    /**
     * Devuelve los items del carrito con el producto cargado y subtotal calculado.
     */
    public function items(): Collection
    {
        $cart = $this->raw();

        if (empty($cart)) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', array_keys($cart))
            ->where('is_published', true)
            ->get()
            ->map(function (Product $product) use ($cart) {
                $quantity = $cart[$product->id];

                return (object) [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => round($product->sale_price * $quantity, 2),
                ];
            });
    }

    public function subtotal(): float
    {
        return round($this->items()->sum('subtotal'), 2);
    }
}

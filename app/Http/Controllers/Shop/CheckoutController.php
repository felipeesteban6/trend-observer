<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\MercadoPagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function create(CartService $cart): Response|RedirectResponse
    {
        $items = $cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('shop.cart')->with('error', 'Tu carrito está vacío.');
        }

        return Inertia::render('Shop/Checkout', [
            'items' => $items,
            'subtotal' => $cart->subtotal(),
        ]);
    }

    public function store(Request $request, CartService $cart, MercadoPagoService $mercadoPago): RedirectResponse
    {
        $items = $cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('shop.cart')->with('error', 'Tu carrito está vacío.');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'region' => ['required', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country_code' => ['required', 'string', 'size:2'],
        ]);

        $subtotal = $cart->subtotal();

        $order = DB::transaction(function () use ($validated, $items, $subtotal) {
            $order = Order::create([
                'order_number' => (string) Str::uuid(),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'shipping_address' => [
                    'street' => $validated['street'],
                    'city' => $validated['city'],
                    'region' => $validated['region'],
                    'postal_code' => $validated['postal_code'] ?? null,
                    'country_code' => strtoupper($validated['country_code']),
                ],
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => $subtotal,
                'shipping_cost' => 0,
                'total' => $subtotal,
                'currency' => $items->first()->product->currency,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'supplier_product_id' => $item->product->supplierTrendingProduct?->supplier_product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->product->sale_price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            return $order;
        });

        $order->load('items');
        $checkoutUrl = $mercadoPago->createPreference($order);

        if (! $checkoutUrl) {
            $order->update(['status' => Order::STATUS_PAYMENT_FAILED]);
            return redirect()->route('shop.checkout')->with('error', 'No pudimos iniciar el pago. Intentá de nuevo en unos minutos.');
        }

        $cart->clear();

        return Inertia::location($checkoutUrl);
    }

    public function confirmation(Order $order): Response
    {
        return Inertia::render('Shop/Confirmation', [
            'order' => $order->load('items'),
        ]);
    }
}

<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchKeywordController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Webhooks\MercadoPagoWebhookController;
use Illuminate\Support\Facades\Route;

// Catálogo público — sin login, es la tienda de cara al cliente.
Route::prefix('tienda')->name('shop.')->group(function () {
    Route::get('/', [CatalogController::class, 'index'])->name('index');
    Route::get('/producto/{product:slug}', [CatalogController::class, 'show'])->name('show');

    Route::get('/carrito', [CartController::class, 'index'])->name('cart');
    Route::post('/carrito', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/carrito/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/carrito/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pedido/{order:order_number}', [CheckoutController::class, 'confirmation'])->name('order.confirmation');
});

Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])->name('webhooks.mercadopago');

// Panel del observador de tendencias — requiere login.
Route::get('/', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/keywords', [SearchKeywordController::class, 'index'])->name('keywords.index');
    Route::post('/keywords', [SearchKeywordController::class, 'store'])->name('keywords.store');
    Route::patch('/keywords/{keyword}', [SearchKeywordController::class, 'update'])->name('keywords.update');
    Route::delete('/keywords/{keyword}', [SearchKeywordController::class, 'destroy'])->name('keywords.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

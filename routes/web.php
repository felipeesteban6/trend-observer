<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchKeywordController;
use Illuminate\Support\Facades\Route;

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

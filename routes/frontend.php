<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\frontend\HomeController;

// Routes للـ Frontend
Route::get('/class/{slug}', [HomeController::class, 'showClass'])->name('frontend.class.show');

// Checkout Routes (require auth)
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [HomeController::class, 'checkout'])->name('frontend.checkout');
    Route::post('/checkout/process', [HomeController::class, 'processCheckout'])->name('frontend.checkout.process');
    
    // Payment Routes
    Route::get('/payment/{purchaseId}', [HomeController::class, 'showPayment'])->name('frontend.payment');
    Route::post('/payment/{purchaseId}/process', [HomeController::class, 'processPayment'])->name('frontend.payment.process');
});

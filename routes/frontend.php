<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\frontend\HomeController;

// Routes للـ Frontend
Route::get('/class/{slug}', [HomeController::class, 'showClass'])->name('frontend.class.show');

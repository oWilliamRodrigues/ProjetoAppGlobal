<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1')
    ->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
    Route::post('/products/sync', [ProductController::class, 'syncFromApi'])->name('api.products.sync');
    Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock'])
        ->name('api.products.update-stock');
});

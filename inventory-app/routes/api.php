<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MercadoPagoWebhookController;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1')
    ->name('api.login');
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::post('/checkout/webhook', [MercadoPagoWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('api.checkout.webhook');
Route::get('/shopcart', [ProductController::class, 'getShopcart'])->name('api.shopcart');
Route::post('/checkout', [OrderController::class, 'checkout'])->name('api.checkout');

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::post('/products/sync', [ProductController::class, 'syncFromApi'])->name('api.products.sync');
    Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock'])
        ->name('api.products.update-stock');
    Route::get('/orders', [OrderController::class, 'indexOrders'])->name('api.orders.index');
    Route::post('/orders/{order}/approve', [OrderController::class, 'approveOrder'])->name('api.orders.approve');
    Route::post('/orders/{order}/discard', [OrderController::class, 'discardOrder'])->name('api.orders.discard');
});
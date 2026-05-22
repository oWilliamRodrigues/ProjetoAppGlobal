<?php

use Illuminate\Support\Facades\Route;

Route::get('/checkout/retorno/{status}', fn ($status) => view('app'))
    ->name('checkout.return');

Route::get('/{any}', fn () => view('app'))->where('any', '^(?!api).*$');
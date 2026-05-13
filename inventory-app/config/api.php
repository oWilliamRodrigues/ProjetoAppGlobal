<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
    */
    'url' => env('API_URL', 'https://fakestoreapi.com'),
    'pagination' => [
        'default_per_page' => (int) env('DEFAULT_PER_PAGE', 15),
        'max_per_page' => (int) env('MAX_PER_PAGE', 100),
    ],
];
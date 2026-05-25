<?php

namespace App\Providers;

use App\Services\Api;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\MercadoPagoService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Api::class, function (Application $app) {
            return $app->makeWith(Api::class, ['baseUrl' => config("api.url")]);
        });

        $this->app->bind(PaymentGatewayInterface::class, MercadoPagoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}

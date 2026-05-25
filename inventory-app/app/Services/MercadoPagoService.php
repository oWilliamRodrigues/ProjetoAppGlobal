<?php

namespace App\Services;

use App\Models\Order;
use App\Services\Contracts\PaymentGatewayInterface;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use Illuminate\Support\Facades\Log;

class MercadoPagoService implements PaymentGatewayInterface
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('services.mercado_pago.access_token'));
        MercadoPagoConfig::setRuntimeEnviroment(
            app()->isProduction()
                ? MercadoPagoConfig::SERVER
                : MercadoPagoConfig::LOCAL
        );
    }

    public function createPreference(Order $order): array
    {
        $order->loadMissing('items.product');

        $items = $order->items->map(fn ($item) => [
            'id'          => (string) $item->product_id,
            'title'       => $item->product->title,
            'quantity'    => (int) $item->quantity,
            'currency_id' => 'BRL',
            'unit_price'  => (float) $item->unit_price,
        ])->values()->toArray();

        try {
            $preference = (new PreferenceClient())->create([
                'items'              => $items,
                'payer'              => ['email' => $order->user_email],
                'external_reference' => (string) $order->id,
                'back_urls'          => [
                    'success' => route('checkout.return', ['status' => 'success']),
                    'failure' => route('checkout.return', ['status' => 'failure']),
                    'pending' => route('checkout.return', ['status' => 'pending']),
                ],
                // 'auto_return'        => 'approved',
                // 'notification_url'   => route('api.checkout.webhook'),
            ]);

            return [
                'preference_id' => $preference->id,
                'init_point'    => $preference->init_point,
            ];
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            Log::error('Erro ao criar preferência de pagamento', [
                'status'  => $apiResponse?->getStatusCode(),
                'content' => $apiResponse?->getContent(),
                'message' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Erro ao criar preferência de pagamento: ' .
                ($apiResponse?->getContent()['message'] ?? $e->getMessage()),
                previous: $e
            );
        }
    }

    public function getPayment(string $paymentId): array
    {
        try {
            $client = new \MercadoPago\Client\Payment\PaymentClient();
            $payment = $client->get($paymentId);

            return [
                'id' => $payment->id,
                'status' => $payment->status,       
                'external_reference' => $payment->external_reference,
            ];
        } catch (MPApiException $e) {
            throw new \RuntimeException('Erro ao consultar pagamento: ' . $e->getMessage(), previous: $e);
        }
    }
}

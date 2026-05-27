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

        $webhookUrl = env('MERCADO_PAGO_NOTIFICATION_URL', route('api.checkout.webhook'));

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
                'notification_url' => $webhookUrl,
            ]);

            return [
                'preference_id' => $preference->id,
                'sandbox_init_point'=> $preference->sandbox_init_point,
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

    public function refund(string $paymentId): array
    {
        try {
            $client = new \MercadoPago\Client\Payment\PaymentRefundClient();
    
            $options = new \MercadoPago\Client\Common\RequestOptions();
            $options->setCustomHeaders([
                "X-Idempotency-Key: " . \Illuminate\Support\Str::uuid()->toString(),
            ]);
    
            $refund = $client->refundTotal((int) $paymentId, $options);
    
            return [
                'id'     => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount,
            ];
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            Log::error('Erro ao processar reembolso', [
                'payment_id' => $paymentId,
                'status'     => $apiResponse?->getStatusCode(),
                'content'    => $apiResponse?->getContent(),
                'message'    => $e->getMessage(),
            ]);
    
            throw new \RuntimeException(
                'Erro ao processar reembolso: ' .
                ($apiResponse?->getContent()['message'] ?? $e->getMessage()),
                previous: $e
            );
        }
    }
}

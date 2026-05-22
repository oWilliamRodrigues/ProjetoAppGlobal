<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Order;
use App\Models\Enums\Status;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Log;    

class MercadoPagoWebhookController extends Controller
{
    public function __construct(private readonly MercadoPagoService $mp) {}

    public function handle(Request $request): Response
    {
        if (! $this->isSignatureValid($request)) {
            Log::warning('Webhook MP: assinatura inválida', ['ip' => $request->ip()]);
            return response('Invalid signature', 401);
        }

        $type = $request->input('type');
        $paymentId = $request->input('data.id');

        if ($type !== 'payment' || ! $paymentId) {
            return response('Ignored', 200); // 200 evita reenvios
        }

        $payment = $this->mp->getPayment((string) $paymentId);

        $order = Order::find($payment['external_reference']);
        if (! $order) {
            return response('Order not found', 200);
        }

        if (in_array($order->mp_payment_status, ['approved', 'rejected', 'cancelled'], true)) {
            return response('Already processed', 200);
        }

        $order->update([
            'mp_payment_id'     => $payment['id'],
            'mp_payment_status' => $payment['status'],
        ]);

        return response('OK', 200);
    }

    private function isSignatureValid(Request $request): bool
    {
        $signatureHeader = $request->header('x-signature');
        $requestId = $request->header('x-request-id');
        $dataId = $request->input('data.id');
        $secret = config('services.mercado_pago.webhook_secret');

        if (! $signatureHeader || ! $requestId || ! $dataId || ! $secret) {
            return false;
        }

        preg_match('/ts=([^,]+),v1=([^,]+)/', $signatureHeader, $m);
        if (count($m) !== 3) return false;
        [$_, $ts, $v1] = $m;

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $v1);
    }
}

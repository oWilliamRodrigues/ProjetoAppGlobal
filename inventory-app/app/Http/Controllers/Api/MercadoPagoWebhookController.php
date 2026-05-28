<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\OrderStatusTranslateService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use App\Models\Order;
use Illuminate\Support\Facades\Log;    
use Illuminate\Support\Facades\DB;

class MercadoPagoWebhookController extends Controller
{
    public function __construct(private readonly PaymentGatewayInterface $mp, private readonly OrderStatusTranslateService $transitions) {}

    public function handle(Request $request): Response
    {
        $type = $request->input('type');
        $paymentId = $request->input('data.id');

        if ($type !== 'payment' || ! $paymentId) {
            return response('Ignored', SymfonyResponse::HTTP_OK); // 200 evita reenvios
        }
        
        try {
            $payment = $this->mp->getPayment((string) $paymentId);
        } catch (\RuntimeException $e) {
            Log::error('Webhook MP: erro ao buscar pagamento', ['payment_id' => $paymentId, 'error' => $e->getMessage()]);
            return response('Error', SymfonyResponse::HTTP_BAD_GATEWAY);
        }

        $found = DB::transaction(function () use ($payment) {
            $order = Order::where('id', $payment['external_reference'])->lockForUpdate()->first();

            if (! $order) {
                return false;
            }

            if ($order->mp_payment_id === $payment['id'] && $order->mp_payment_status === $payment['status']) {
                return false; 
            }

            $order->mp_payment_id = $payment['id'];
            $order->mp_payment_status = $payment['status'];

            $this->transitions->apply($order, $payment['status']);
            
            $order->save();

            return true;
        });

        return $found ? response('OK', SymfonyResponse::HTTP_OK) : response('Order not found', SymfonyResponse::HTTP_NOT_FOUND);
    }
}

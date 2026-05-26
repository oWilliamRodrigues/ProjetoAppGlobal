<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderStatusTranslateService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Order;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Log;    
use Illuminate\Support\Facades\DB;

class MercadoPagoWebhookController extends Controller
{
    public function __construct(private readonly MercadoPagoService $mp, private readonly OrderStatusTranslateService $transitions) {}

    public function handle(Request $request): Response
    {
        $type = $request->input('type');
        $paymentId = $request->input('data.id');

        if ($type !== 'payment' || ! $paymentId) {
            return response('Ignored', 200); // 200 evita reenvios
        }
        
        try {
            $payment = $this->mp->getPayment((string) $paymentId);
        } catch (\RuntimeException $e) {
            Log::error('Webhook MP: erro ao buscar pagamento', ['payment_id' => $paymentId, 'error' => $e->getMessage()]);
            dd($e);
            return response('Error', 502);
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

        return $found ? response('OK', 200) : response('Order not found', 200);
    }
}

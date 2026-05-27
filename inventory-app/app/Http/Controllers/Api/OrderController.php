<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Services\OrderCheckoutService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public readonly OrderCheckoutService $checkoutService;

    public function __construct(OrderCheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function checkout(StoreOrderRequest $request) : JsonResponse
    {
        $data = $request->validated();

        $result = $this->checkoutService->checkoutWithPayment($data['items'], $data['user_email']);

        return response()->json([
            'order' => $result['order'],
            'preference_id' => $result['preference_id'],
            'sandbox_init_point' => $result['sandbox_init_point'],
        ], 201);
    }

        public function indexOrders() : JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $pending = Order::where('status', Status::AGUARDANDO->value)
            ->with('items.product')
            ->orderByDesc('id')
            ->paginate(15);

        $approved = Order::where('status', Status::APROVADO->value)
            ->with('items.product')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return response()->json([
            'pending' => $pending,
            'approved' => $approved
        ]);
    }

    public function approveOrder(Order $order): JsonResponse
    {
        $this->authorize('approve', $order);

        $this->orderApproval->approve($order);

        return response()->json(['message' => 'Pedido aprovado com sucesso.']);
    }

    public function discardOrder(Order $order): JsonResponse
    {
        $this->authorize('discard', $order);

        $this->orderApproval->discard($order);

        return response()->json(['message' => 'Pedido descartado com sucesso.']);
    }
}
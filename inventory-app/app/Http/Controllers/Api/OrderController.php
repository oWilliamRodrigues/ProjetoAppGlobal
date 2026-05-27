<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Services\OrderCheckoutService;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use App\Models\Enums\Status;
use App\Services\OrderApprovalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public readonly OrderCheckoutService $checkoutService;
    private readonly OrderApprovalService $orderApproval;

    public function __construct(OrderCheckoutService $checkoutService, OrderApprovalService $orderApproval)
    {
        $this->checkoutService = $checkoutService;
        $this->orderApproval = $orderApproval;
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

    public function indexOrders(Request $request) : JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(Status::class)],
        ]);

        $orders = Order::where('status', $validated['status'])->with('items.product')->orderByDesc('id')->paginate(15);

        return response()->json($orders);
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
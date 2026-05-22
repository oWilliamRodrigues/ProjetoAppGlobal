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
            'init_point' => $result['init_point'],
        ], 201);
    }
}
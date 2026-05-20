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
        $order = $this->checkoutService->checkout($data['items'], $data['user_email']);

        return response()->json(['order' => $order], 201);
    }
}
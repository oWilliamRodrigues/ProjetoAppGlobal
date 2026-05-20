<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function checkout(StoreOrderRequest $request){
        $items = $request->validated()['items'];
        $ids = array_column($items, 'product_id');

        $order = DB::transaction(function () use ($ids, $request, $items){
            $products = Product::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            if ($products->count() !== count($ids)) {
                throw ValidationException::withMessages([
                    'items' => 'Um ou mais produtos não foram encontrados.',
                ]);
            }

            $total = 0;

            foreach ($items as $item) {
                $product = $products[ $item['product_id'] ];

                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Estoque insuficiente para o produto {$product->title}.",
                    ]);
                }

                $product->stock_quantity -= $item['quantity'];
                $product->save();

                $total += $product->price * $item['quantity'];
            }

            $order = Order::create([
                'user_email' => $request->validated()['user_email'],
                'amount' => $total,
            ]);

            foreach ($items as $item) {
                $order->orderItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price'=> $products[$item['product_id']]['price'],
                ]);
            }
        return $order->load('orderItems');
        });
    return response()->json(['order' => $order], 201);
    }
}
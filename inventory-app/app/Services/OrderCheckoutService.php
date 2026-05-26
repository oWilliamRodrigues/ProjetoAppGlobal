<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Product;
use App\Services\Contracts\PaymentGatewayInterface;


class OrderCheckoutService {
    public function __construct(private readonly PaymentGatewayInterface $gateway)
    {
    }

    public function checkoutWithPayment(array $items, string $userEmail): array{
        $order = $this->checkout($items, $userEmail);

        $preference = $this->gateway->createPreference($order);

        $order->update(['mp_preference_id' => $preference['preference_id']]);
        return[
            'order' => $order,
            'preference_id' => $preference['preference_id'],
            'sandbox_init_point' => $preference['sandbox_init_point'],
        ];
    }

    public function checkout(array $items, string $userEmail): Order{

        $ids = array_column($items, 'product_id');

        $order = DB::transaction(function () use ($ids, $userEmail, $items){
            $products = Product::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            if ($products->count() !== count($ids)) {
                throw ValidationException::withMessages([
                    'items' => 'Um ou mais produtos não foram encontrados.',
                ]);
            }

            $total = 0;

            foreach ($items as $item) {
                $product = $products[ $item['product_id'] ];

                $product->save();

                $total += $product->price * $item['quantity'];
            }

            $order = Order::create([
                'user_email' => $userEmail,
                'amount' => $total,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price'=> $products[$item['product_id']]['price'],
                ]);
            }
            return $order;
        });
        return $order;
    }
}
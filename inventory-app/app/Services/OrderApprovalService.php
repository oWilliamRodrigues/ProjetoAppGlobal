<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Enums\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderApprovalService {
    public function approve(Order $order) : void 
    {
        DB::transaction(function () use ($order)
        {
            $productIds = $order->items->pluck('product_id');

            $products = Product::whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

            foreach ($order->items as $item) {
                $product = $products[$item->product_id];

                if($product->stock_quantity < $item->quantity) {
                    throw ValidationException::withMessages(['stock' => "Estoque insuficiente para o produto ID {$item->product_id}.",]);
                }
            }

            foreach ($order->items as $item) {
                $products[$item->product_id]->decrement('stock_quantity', $item->quantity);
            }

            // 5. Atualizar status do pedido
            $order->update(['status' => Status::APROVADO]);
        
        });
    }

    public function discard(Order $order) : void 
    {
        DB::transaction(function () use ($order) {
            $order->update(['status' => Status::DESCARTADO]);
        });
    }
}
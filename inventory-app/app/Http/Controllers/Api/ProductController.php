<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\UpdateStockRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $perPage = (int) $request->integer('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $products = Product::query()
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json($products);
    }

    public function updateStock(UpdateStockRequest $request, Product $product): JsonResponse
    {
        $op = $request->validated('operation');
        $qty = (int) $request->validated('quantity');

        $updated = DB::transaction(function () use ($product, $op, $qty) {
            $fresh = Product::lockForUpdate()->findOrFail($product->id);

            $newQty = match ($op) {
                'set' => $qty,
                'increment' => $fresh->stock_quantity + $qty,
                'decrement' => $fresh->stock_quantity - $qty,
            };

            if ($newQty < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Estoque insuficiente: atual {$fresh->stock_quantity}, tentativa de remover {$qty}.",
                ]);
            }

            $fresh->stock_quantity = $newQty;
            $fresh->save();

            return $fresh;
        });

        return response()->json(['product' => $updated]);
    }

    public function syncFromApi(): JsonResponse
    {
        $response = $this->api->get('/products');

        if ($response === false) {
            return response()->json(['message' => 'Falha ao buscar produtos da API externa.'], 502);
        }
        
        $data = json_decode($response, true);

        Product::whereNotIn('external_id', collect($data)->pluck('id'))->delete();

        return response()->json(['message' => 'Produtos sincronizados com sucesso.']);
    }
}

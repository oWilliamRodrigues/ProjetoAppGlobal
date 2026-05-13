<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\UpdateStockRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\ProductSyncService;

class ProductController extends Controller
{
    public function __construct(private readonly ProductSyncService $sync)
    {
    }
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $default = (int) config('api.pagination.default_per_page');

        $perPage = (int) $request->integer('per_page', $default);

        $products = Product::query()
            ->orderBy('title')
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
        dd("coiso");
        try {
            $stats = $this->sync->sync();

            return response()->json([
                'message' => 'Produtos sincronizados com sucesso.',
                'stats' => $stats,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }
}

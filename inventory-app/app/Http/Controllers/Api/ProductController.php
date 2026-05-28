<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\ProductSyncService;
use App\Http\Requests\Products\UpdateStockRequest;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductSyncService $sync,
    ) {
    }
    
    public function index(Request $request): JsonResponse
    {
        $default = (int) config('api.pagination.default_per_page');
        $perPage = (int) $request->integer('per_page', $default);
        $search = $request->query('search');

        $products = Product::query()
            ->orderBy('title')
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->paginate($perPage)
            ->appends($request->only('search', 'per_page'));
        
        return response()->json($products);

    }

    public function updateStock(UpdateStockRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();
        $operation = $validated['operation'];
        $quantity = (int) $validated['quantity'];

        $updated = DB::transaction(function () use ($product, $operation, $quantity) {
            $fresh = Product::lockForUpdate()->findOrFail($product->id);

            $newQty = match ($operation) {
                'set' => $quantity,
                'increment' => $fresh->stock_quantity + $quantity,
                'decrement' => $fresh->stock_quantity - $quantity,
            };

            if ($newQty < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Estoque insuficiente: atual {$fresh->stock_quantity}, tentativa de remover {$quantity}.",
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
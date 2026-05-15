<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProductSyncService
{
    public function sync(): array
    {
        $response = Http::timeout(10)->retry(2, 500)->get(config('services.fake_store.url'). '/products');

        if ($response->failed()) {
            throw new \RuntimeException('Falha ao buscar produtos da API externa.');
        }

        $data = $response->json();

        return DB::transaction(function () use ($data) {
            $created = 0;
            $updated = 0;

            foreach ($data as $product) {
                $model = Product::withTrashed()->updateOrCreate(
                    ['external_id' => $product['id']],
                    [
                        'external_id' => $product['id'],
                        'title' => $product['title'],
                        'price' => $product['price'],
                        'description' => $product['description'],
                        'category' => $product['category'],
                        'image_url' => $product['image'],
                        'rating_rate' => $product['rating']['rate'] ?? null,
                        'rating_count' => $product['rating']['count'] ?? null,
                    ]
                );

                if ($model->trashed()) {
                    $model->restore();
                }

                $model->wasRecentlyCreated ? $created++ : $updated++;
            }

            $deleted = Product::whereNotIn('external_id', collect($data)->pluck('id'))->delete();

            return [
                'created' => $created,
                'updated' => $updated,
                'deleted' => $deleted,
            ];
        });
    }
}
<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductSyncService
{
    public function __construct(private readonly Api $api)
    {
    }

    public function sync(): array
    {
        $response = $this->api->get('/products');

        if ($response === false) {
            throw new \RuntimeException('Falha ao buscar produtos da API externa.');
        }

        $data = json_decode($response, true);

        return DB::transaction(function () use ($data) {
            $created = 0;
            $updated = 0;

            foreach ($data as $product) {
                $model = Product::updateOrCreate(
                    ['external_id' => $product['id']],
                    [
                        'external_id' => $product['id'],
                        'title' => $product['title'],
                        'price' => $product['price'],
                        'description' => $product['description'],
                        'category' => $product['category'],
                        'image' => $product['image'],
                        'rating_rate' => $product['rating']['rate'] ?? null,
                        'rating_count' => $product['rating']['count'] ?? null,
                    ]
                );

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
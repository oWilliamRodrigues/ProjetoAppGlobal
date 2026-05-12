<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'external_id',
        'title',
        'description',
        'price',
        'category',
        'image_url',
        'rating_rate',
        'rating_count',
        'stock_quantity',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'rating_rate' => 'decimal:1',
            'rating_count' => 'integer',
        ];
    }
}

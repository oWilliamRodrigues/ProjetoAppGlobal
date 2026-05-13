<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{ 
    /** @use HasFactory<ProductFactory> */
    use HasFactory; use SoftDeletes;

    protected $fillable = [
        'external_id',
        'title',
        'price',
        'description',
        'category',
        'image',
        'stock_quantity',
        'rating_rate',
        'rating_count',
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

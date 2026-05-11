<?php

namespace App\Models;

<<<<<<< adam
=======
use Database\Factories\ProductFactory;
>>>>>>> feat/Fundacao
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
<<<<<<< adam
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
=======
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'stock_quantity',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
        ];
    }
>>>>>>> feat/Fundacao
}

<?php

namespace App\Models;

use App\Models\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model{
    
    use HasFactory;

    protected $fillable = [
        "order_id",
        "user_email",
        "status",
        "amount",
    ];

    protected function casts(): array
    {
        return [
            "user_email" => "string",
            'amount' => 'decimal:2',
            'status' => Status::class
        ];
    }

    protected $attributes = [
        'status' => Status::AGUARDANDO,
    ];
}
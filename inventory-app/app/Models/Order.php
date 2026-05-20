<?php

namespace App\Models;

use App\Models\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model{
    
    use HasFactory;

    protected $fillable = [
        "user_email",
        "status",
        "amount",
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => Status::class,
        ];
    }

    protected $attributes = [
        'status' => Status::AGUARDANDO,
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
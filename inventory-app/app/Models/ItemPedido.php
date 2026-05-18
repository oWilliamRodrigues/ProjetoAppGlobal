<?php

class ItemPedido extends Model {
    protected $fillable = [
        'id',
        'pedido_id',
        'produto_id',
        'quantidade',
        'preco_unitario',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'preco_unitario' => 'decimal:2',
        ];
    }
}
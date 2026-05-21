<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Order;

class StoreOrderRequest extends FormRequest{

    public function authorize(){
        return true;
    }

    public function rules(){
        return [
            'user_email' => ['required', 'email', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(){
        return [
            'user_email.required' => 'O campo email do usuário é obrigatório.',
            'user_email.email' => 'O campo email do usuário deve ser um endereço de email válido.',
            'user_email.max' => 'O campo email do usuário não pode ter mais que 255 caracteres.',
            'items.required' => 'O campo itens é obrigatório.',
            'items.array' => 'O campo itens deve ser um array.',
            'items.min' => 'O campo itens deve ter pelo menos 1 item.',
            'items.*.product_id.required' => 'O campo ID do produto é obrigatório.',
            'items.*.product_id.integer' => 'O campo ID do produto deve ser um número inteiro.',
            'items.*.product_id.exists' => 'O produto selecionado não existe.',
            'items.*.quantity.required' => 'O campo quantidade é obrigatório.',
            'items.*.quantity.integer' => 'O campo quantidade deve ser um número inteiro.',
            'items.*.quantity.min' => 'O campo quantidade deve ser pelo menos 1.',
        ];
    }
}
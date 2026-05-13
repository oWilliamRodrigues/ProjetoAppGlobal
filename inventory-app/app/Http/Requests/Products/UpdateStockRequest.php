<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('updateStock', $this->route('product')) ?? false;
    }

    public function rules(): array
    {
        return [
            'operation' => ['required', Rule::in(['set', 'increment', 'decrement'])],
            'quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'operation.in' => "operation deve ser 'set', 'increment' ou 'decrement'.",
            'quantity.integer' => 'quantity deve ser número inteiro.',
            'quantity.min' => 'quantity não pode ser negativo.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $op = $this->input('operation');
            $qty = (int) $this->input('quantity', 0);

            if (in_array($op, ['increment', 'decrement'], true) && $qty < 1) {
                $validator->errors()->add('quantity', 'Para increment/decrement, quantity deve ser >= 1.');
            }
        });
    }
}

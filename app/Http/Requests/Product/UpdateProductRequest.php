<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(['physical', 'digital', 'service', 'subscription'])],
            'category' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'long_description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'sku' => ['nullable', 'string', 'max:100'],
            'stock_status' => ['nullable', Rule::in(['in_stock', 'out_of_stock', 'preorder'])],
            'product_url' => ['nullable', 'url', 'max:255'],
            'key_benefits' => ['nullable', 'array'],
            'technical_specs' => ['nullable', 'array'],
            'target_persona_tags' => ['nullable', 'array'],
            'problem_it_solves' => ['nullable', 'string'],
            'objections' => ['nullable', 'array'],
            'faqs' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Ürün adı metin formatında olmalıdır.',
            'name.max' => 'Ürün adı en fazla :max karakter olabilir.',
            'type.in' => 'Ürün tipi geçerli bir değer olmalıdır (physical, digital, service, subscription).',
            'price.numeric' => 'Fiyat sayısal bir değer olmalıdır.',
            'price.min' => 'Fiyat en az :min olmalıdır.',
            'discount_price.numeric' => 'İndirimli fiyat sayısal bir değer olmalıdır.',
            'discount_price.min' => 'İndirimli fiyat en az :min olmalıdır.',
            'currency.size' => 'Para birimi 3 karakter olmalıdır.',
            'stock_status.in' => 'Stok durumu geçerli bir değer olmalıdır.',
            'product_url.url' => 'Ürün URL\'si geçerli bir URL olmalıdır.',
        ];
    }
}

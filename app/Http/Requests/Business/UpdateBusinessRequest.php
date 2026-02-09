<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'sub_sector' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'logo_path' => ['nullable', 'string'],
            'brand_story' => ['nullable', 'string'],
            'brand_tone' => ['nullable', Rule::in(['serious', 'friendly', 'luxury', 'fun', 'youth', 'professional', 'casual', 'energetic'])],
            'brand_voice_rules' => ['nullable', 'string'],
            'forbidden_words' => ['nullable', 'array'],
            'competitor_names' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'İşletme adı metin formatında olmalıdır.',
            'name.max' => 'İşletme adı en fazla :max karakter olabilir.',
            'website.url' => 'Website geçerli bir URL olmalıdır.',
            'brand_tone.in' => 'Marka tonu geçerli bir değer olmalıdır.',
        ];
    }
}

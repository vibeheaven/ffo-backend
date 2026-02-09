<?php

namespace App\Http\Requests\Quota;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quota' => ['required', 'integer', 'min:0'],
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'quota.required' => 'Kota değeri zorunludur.',
            'quota.integer' => 'Kota değeri tam sayı olmalıdır.',
            'quota.min' => 'Kota değeri en az :min olabilir.',
            'user_id.integer' => 'Kullanıcı ID tam sayı olmalıdır.',
            'user_id.exists' => 'Belirtilen kullanıcı bulunamadı.',
        ];
    }
}

<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi', 'max:10240'], // Max 10MB
            'type' => ['nullable', Rule::in(['image', 'video', 'manual'])],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Dosya zorunludur.',
            'file.file' => 'Geçerli bir dosya yüklenmelidir.',
            'file.mimes' => 'Dosya formatı geçersiz. İzin verilen formatlar: jpeg, jpg, png, gif, webp, mp4, mov, avi',
            'file.max' => 'Dosya boyutu en fazla :max KB olabilir.',
            'type.in' => 'Medya tipi geçerli bir değer olmalıdır (image, video, manual).',
        ];
    }
}

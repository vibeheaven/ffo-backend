<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $projectId = $this->route('project');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'token' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('projects', 'token')->ignore($projectId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Proje adı metin formatında olmalıdır.',
            'name.max' => 'Proje adı en fazla :max karakter olabilir.',
            'token.string' => 'Proje tokenı metin formatında olmalıdır.',
            'token.max' => 'Proje tokenı en fazla :max karakter olabilir.',
            'token.unique' => 'Bu token zaten kullanılıyor.',
            'description.string' => 'Açıklama metin formatında olmalıdır.',
            'description.max' => 'Açıklama en fazla :max karakter olabilir.',
        ];
    }
}

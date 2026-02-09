<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                $this->noConsecutiveNumbers(),
                $this->noRepeatedCharacters(),
            ],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'birthday' => ['nullable', 'date'],
            'location' => ['nullable', 'string'],
            'language' => ['nullable', 'string', 'min:2', 'max:5'],
        ];
    }

    /** Ardışık sayılar (123, 234, 987 vb.) içeremez. */
    protected function noConsecutiveNumbers(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $asc = ['012', '123', '234', '345', '456', '567', '678', '789'];
            $desc = ['987', '876', '765', '654', '543', '432', '321', '210'];
            foreach (array_merge($asc, $desc) as $seq) {
                if (str_contains((string) $value, $seq)) {
                    $fail('Şifre ardışık sayılar (örn. 123, 789) içeremez.');
                    return;
                }
            }
        };
    }

    /** Aynı karakterin 3 veya daha fazla tekrarı (aaa, 111 vb.) olamaz. */
    protected function noRepeatedCharacters(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (preg_match('/(.)\1{2,}/', (string) $value)) {
                $fail('Şifre aynı karakterin üç veya daha fazla tekrarını içeremez.');
            }
        };
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Şifre alanı zorunludur.',
            'password.confirmed' => 'Şifre tekrarı eşleşmiyor.',
            'password.min' => 'Şifre en az :min karakter olmalıdır.',
            'password.letters' => 'Şifre en az bir harf içermelidir.',
            'password.mixed' => 'Şifre en az bir büyük ve bir küçük harf içermelidir.',
            'password.numbers' => 'Şifre en az bir rakam içermelidir.',
            'password.symbols' => 'Şifre en az bir özel karakter (!@#$%^&* vb.) içermelidir.',
        ];
    }
}

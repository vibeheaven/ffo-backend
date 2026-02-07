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
            'password' => ['required', 'confirmed', Password::defaults()],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            // Made these nullable since they were in step 4, but let's keep them if they are sent. 
            // The user said "single step", assuming all info is sent at once.
            // If they are optional in the UI, they should be nullable here.
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'birthday' => ['nullable', 'date'],
            'location' => ['nullable', 'string'],
            'language' => ['nullable', 'string', 'min:2', 'max:5'],
        ];
    }
}

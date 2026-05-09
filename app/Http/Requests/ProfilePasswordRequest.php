<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfilePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! Hash::check($value, (string) $this->user()?->password)) {
                        $fail('Password saat ini tidak sesuai.');
                    }
                },
            ],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}

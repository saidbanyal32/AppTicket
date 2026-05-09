<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfilePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email_notifications' => $this->boolean('email_notifications'),
            'compact_mode' => $this->boolean('compact_mode'),
        ]);
    }

    public function rules(): array
    {
        return [
            'language' => ['required', Rule::in(['id', 'en'])],
            'timezone' => ['required', 'timezone'],
            'email_notifications' => ['required', 'boolean'],
            'compact_mode' => ['required', 'boolean'],
        ];
    }
}

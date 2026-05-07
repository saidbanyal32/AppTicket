<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $setting = $this->route('setting');
        $id = $setting?->id ?? $setting;

        return [
            'key' => ['required', 'string', 'max:120', 'unique:settings,key,'.($id ?: 'NULL').',id'],
            'value' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ];
    }
}

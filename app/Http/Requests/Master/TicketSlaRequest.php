<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class TicketSlaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'priority' => ['required', 'string', 'max:50'],
            'response_minutes' => ['required', 'numeric', 'min:0'],
            'resolve_minutes' => ['required', 'numeric', 'min:0'],
            'escalation_minutes' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ];
    }
}

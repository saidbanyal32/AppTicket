<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
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
        return [
            'subject' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:ref_ticket_categories,id'],
            'priority' => ['required', 'in:LOW,MEDIUM,HIGH,CRITICAL'],
            'jabatan_id' => ['nullable', 'exists:ref_jabatan,id'],
            'assigned_to' => ['nullable', 'exists:sys_users,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt,zip', 'max:10240'],
        ];
    }
}

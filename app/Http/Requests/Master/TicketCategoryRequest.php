<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $id = is_object($category) ? $category->getKey() : $category;

        return [
            'parent_id' => ['nullable', 'exists:ref_ticket_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50', Rule::unique('ref_ticket_categories', 'code')->ignore($id)],
            'color' => ['nullable', 'string', 'max:30'],
            'icon' => ['nullable', 'string', 'max:80'],
            'sla_id' => ['nullable', 'exists:ref_ticket_slas,id'],
            'is_active' => ['nullable', 'boolean'],
            'sort_no' => ['nullable', 'numeric'],
        ];
    }
}

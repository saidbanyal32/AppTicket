<?php

namespace App\Http\Requests;

use App\Models\HelpArticle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HelpArticleRequest extends FormRequest
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
            'category_id' => ['required', 'integer', Rule::exists('help_categories', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', Rule::unique('help_articles', 'slug')->ignore($this->route('article'))],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'article_type' => ['required', 'string', Rule::in(HelpArticle::ARTICLE_TYPES)],
            'visibility' => ['required', 'string', Rule::in(HelpArticle::VISIBILITIES)],
            'tags' => ['nullable', 'string', 'max:500'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,zip,txt'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }
}

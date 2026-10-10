<?php

namespace App\Http\Requests\Admin;

use App\Models\FaqCategory;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFaqCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_ADMIN) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('faq_categories', 'name')->ignore($this->route('faq_category'))],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('faq_categories', 'slug')->ignore($this->route('faq_category'))],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', Rule::in(FaqCategory::statuses())],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }
}

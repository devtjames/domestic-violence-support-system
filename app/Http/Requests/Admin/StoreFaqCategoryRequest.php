<?php

namespace App\Http\Requests\Admin;

use App\Models\FaqCategory;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFaqCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120', 'unique:faq_categories,name'],
            'slug' => ['nullable', 'string', 'max:140', 'unique:faq_categories,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', Rule::in(FaqCategory::statuses())],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }
}

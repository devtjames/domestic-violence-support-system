<?php

namespace App\Http\Requests\Admin;

use App\Models\AwarenessCategory;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAwarenessCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_ADMIN) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $category = $this->route('awareness_category');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('awareness_categories', 'name')->ignore($category)],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('awareness_categories', 'slug')->ignore($category)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(AwarenessCategory::statuses())],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}

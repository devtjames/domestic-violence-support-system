<?php

namespace App\Http\Requests\Admin;

use App\Models\AwarenessArticle;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAwarenessArticleRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('awareness_articles', 'slug')],
            'awareness_category_id' => ['required', 'integer', Rule::exists('awareness_categories', 'id')->whereNull('deleted_at')],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'short_description' => ['required', 'string', 'min:20', 'max:500'],
            'content' => ['required', 'string', 'min:50'],
            'status' => ['required', Rule::in(AwarenessArticle::statuses())],
            'published_at' => ['nullable', 'date'],
        ];
    }
}

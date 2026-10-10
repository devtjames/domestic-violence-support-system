<?php

namespace App\Http\Requests\Admin;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFaqRequest extends FormRequest
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
            'faq_category_id' => ['required', Rule::exists('faq_categories', 'id')],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'min:10', 'max:3000'],
            'status' => ['required', 'string', Rule::in(Faq::statuses())],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }
}

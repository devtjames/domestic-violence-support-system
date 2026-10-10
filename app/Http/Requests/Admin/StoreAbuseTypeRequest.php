<?php

namespace App\Http\Requests\Admin;

use App\Models\AbuseType;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAbuseTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120', Rule::unique('abuse_types', 'name')],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('abuse_types', 'slug')],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(AbuseType::statuses())],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}

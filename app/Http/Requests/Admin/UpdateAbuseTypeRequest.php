<?php

namespace App\Http\Requests\Admin;

use App\Models\AbuseType;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAbuseTypeRequest extends FormRequest
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
        $abuseType = $this->route('abuse_type');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('abuse_types', 'name')->ignore($abuseType)],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('abuse_types', 'slug')->ignore($abuseType)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(AbuseType::statuses())],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}

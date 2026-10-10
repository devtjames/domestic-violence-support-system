<?php

namespace App\Http\Requests\Admin;

use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManagedSupportCaseRequest extends FormRequest
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
            'assigned_officer_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', User::ROLE_SUPPORT_OFFICER)
                    ->where('account_status', User::STATUS_ACTIVE)),
            ],
            'urgency' => ['required', 'string', Rule::in(SupportCase::urgencies())],
            'status' => ['required', 'string', Rule::in(SupportCase::statuses())],
            'resolution_summary' => ['nullable', 'required_if:status,'.SupportCase::STATUS_RESOLVED.','.SupportCase::STATUS_CLOSED, 'string', 'max:2000'],
            'action_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

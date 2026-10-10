<?php

namespace App\Http\Requests\Officer;

use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManagedSupportCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $supportCase = $this->route('support_case');

        return $this->user()?->hasRole(User::ROLE_SUPPORT_OFFICER)
            && $supportCase instanceof SupportCase
            && $supportCase->canBeManagedByOfficer($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'urgency' => ['required', 'string', Rule::in(SupportCase::urgencies())],
            'status' => [
                'required',
                'string',
                Rule::in([
                    SupportCase::STATUS_UNDER_REVIEW,
                    SupportCase::STATUS_IN_PROGRESS,
                    SupportCase::STATUS_AWAITING_USER,
                    SupportCase::STATUS_REFERRED,
                    SupportCase::STATUS_RESOLVED,
                    SupportCase::STATUS_CLOSED,
                ]),
            ],
            'resolution_summary' => ['nullable', 'required_if:status,'.SupportCase::STATUS_RESOLVED.','.SupportCase::STATUS_CLOSED, 'string', 'max:2000'],
            'action_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

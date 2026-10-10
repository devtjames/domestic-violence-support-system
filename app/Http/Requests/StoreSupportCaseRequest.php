<?php

namespace App\Http\Requests;

use App\Models\AbuseType;
use App\Models\CaseAttachment;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportCaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_USER) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'abuse_type_id' => [
                'required',
                'integer',
                Rule::exists('abuse_types', 'id')->where(fn ($query) => $query->where('status', AbuseType::STATUS_ACTIVE)),
            ],
            'subject' => ['required', 'string', 'min:5', 'max:160'],
            'description' => ['required', 'string', 'min:30', 'max:5000'],
            'preferred_contact_method' => ['nullable', 'string', Rule::in(array_keys(SupportCase::contactMethods()))],
            'safe_contact_time' => ['nullable', 'string', 'max:120'],
            'safe_contact_instruction' => ['nullable', 'string', 'max:1000'],
            'urgency' => ['required', 'string', Rule::in(SupportCase::urgencies())],
            ...CaseAttachment::uploadRules(),
        ];
    }

    public function messages(): array
    {
        return [
            'abuse_type_id.exists' => 'Choose an available abuse type.',
            'description.min' => 'Please provide a little more detail so a support officer can review the request appropriately.',
        ] + CaseAttachment::uploadMessages();
    }
}

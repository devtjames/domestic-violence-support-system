<?php

namespace App\Http\Requests;

use App\Models\AbuseType;
use App\Models\CaseAttachment;
use App\Models\SupportCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnonymousSupportCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'anonymous_nickname' => ['nullable', 'string', 'max:80'],
            'abuse_type_id' => [
                'required',
                'integer',
                Rule::exists('abuse_types', 'id')->where(fn ($query) => $query->where('status', AbuseType::STATUS_ACTIVE)),
            ],
            'subject' => ['required', 'string', 'min:5', 'max:160'],
            'description' => ['required', 'string', 'min:30', 'max:5000'],
            'preferred_contact_method' => ['nullable', 'string', Rule::in(array_keys(SupportCase::contactMethods()))],
            'contact_email' => ['nullable', 'email', 'max:160'],
            'contact_phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+().\-\s]{7,40}$/'],
            'safe_contact_instruction' => ['nullable', 'string', 'max:1000'],
            'urgency' => ['required', 'string', Rule::in(SupportCase::urgencies())],
            ...CaseAttachment::uploadRules(),
        ];
    }

    public function messages(): array
    {
        return [
            'abuse_type_id.exists' => 'Choose an available abuse type.',
            'contact_phone.regex' => 'Enter a valid phone number using numbers, spaces, brackets, dashes, or a leading plus sign.',
            'description.min' => 'Please provide a little more detail so the request can be reviewed appropriately.',
        ] + CaseAttachment::uploadMessages();
    }
}

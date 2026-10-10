<?php

namespace App\Http\Requests;

use App\Models\CaseAttachment;
use App\Models\SupportCase;
use Illuminate\Foundation\Http\FormRequest;

class StoreCaseAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $supportCase = $this->route('support_case');

        if (! $supportCase instanceof SupportCase || ! $this->user()) {
            return false;
        }

        if ($supportCase->status === SupportCase::STATUS_CLOSED) {
            return false;
        }

        if ($this->user()->isAdmin()) {
            return true;
        }

        if ($this->user()->isSupportOfficer()) {
            return $supportCase->canBeManagedByOfficer($this->user());
        }

        return $this->user()->isRegisteredUser() && $supportCase->user_id === $this->user()->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return CaseAttachment::uploadRules(required: true);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return CaseAttachment::uploadMessages();
    }
}

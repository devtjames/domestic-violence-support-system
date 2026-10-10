<?php

namespace App\Http\Requests\Officer;

use App\Models\AbuseType;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CaseQueueFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_SUPPORT_OFFICER) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', Rule::in(SupportCase::statuses())],
            'urgency' => ['nullable', 'string', Rule::in(SupportCase::urgencies())],
            'abuse_type' => ['nullable', 'integer', Rule::exists((new AbuseType)->getTable(), 'id')],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort' => ['nullable', 'string', Rule::in($this->sortOptions())],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function sortOptions(): array
    {
        return [
            'newest',
            'oldest',
            'recently_updated',
            'oldest_updated',
            'urgency_high',
            'urgency_low',
            'status',
        ];
    }
}

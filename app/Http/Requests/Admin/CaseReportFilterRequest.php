<?php

namespace App\Http\Requests\Admin;

use App\Models\SupportCase;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CaseReportFilterRequest extends FormRequest
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
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', 'string', Rule::in(SupportCase::statuses())],
            'urgency' => ['nullable', 'string', Rule::in(SupportCase::urgencies())],
            'abuse_type_id' => ['nullable', 'integer', Rule::exists('abuse_types', 'id')],
            'assigned_officer_id' => ['nullable', $this->assignedOfficerRule()],
            'source' => ['nullable', 'string', Rule::in(['registered', 'anonymous'])],
            'workflow' => ['nullable', 'string', Rule::in(['pending', 'resolved'])],
        ];
    }

    private function assignedOfficerRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === 'unassigned') {
                return;
            }

            $exists = User::query()
                ->whereKey($value)
                ->where('role', User::ROLE_SUPPORT_OFFICER)
                ->exists();

            if (! $exists) {
                $fail('Choose a valid support officer or unassigned.');
            }
        };
    }
}

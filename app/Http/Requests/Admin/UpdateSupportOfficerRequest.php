<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupportOfficerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_ADMIN) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $officerId = $this->route('support_officer')?->id;

        return [
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160', Rule::unique(User::class, 'email')->ignore($officerId)],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+().\-\s]{7,40}$/'],
            'gender' => ['nullable', 'string', Rule::in(User::genders())],
            'staff_id' => ['required', 'string', 'max:40', Rule::unique(User::class, 'staff_id')->ignore($officerId)],
            'specialization' => ['required', 'string', Rule::in(User::supportOfficerSpecializations())],
            'account_status' => ['required', 'string', Rule::in(User::accountStatuses())],
            'date_joined' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid phone number using numbers, spaces, brackets, dashes, or a leading plus sign.',
        ];
    }
}

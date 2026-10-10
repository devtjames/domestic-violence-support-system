<?php

namespace App\Http\Requests\Admin;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has(SystemSetting::KEY_CASE_REFERENCE_PREFIX)) {
            $this->merge([
                SystemSetting::KEY_CASE_REFERENCE_PREFIX => strtoupper((string) $this->input(SystemSetting::KEY_CASE_REFERENCE_PREFIX)),
            ]);
        }
    }

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
            SystemSetting::KEY_PLATFORM_NAME => ['required', 'string', 'max:120'],
            SystemSetting::KEY_PLATFORM_EMAIL => ['nullable', 'email', 'max:160'],
            SystemSetting::KEY_SUPPORT_PHONE => ['nullable', 'string', 'max:40', 'regex:/^[0-9+().\-\s]{7,40}$/'],
            SystemSetting::KEY_SUPPORT_EMAIL => ['nullable', 'email', 'max:160'],
            SystemSetting::KEY_ADDRESS => ['nullable', 'string', 'max:500'],
            SystemSetting::KEY_QUICK_EXIT_URL => ['required', 'url', 'max:255', 'regex:/^https?:\/\//i'],
            SystemSetting::KEY_CASE_REFERENCE_PREFIX => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9][A-Z0-9-]{1,9}$/'],
            SystemSetting::KEY_CONTACT_INFORMATION => ['nullable', 'string', 'max:1000'],
            SystemSetting::KEY_CRITICAL_CASE_GUIDANCE => ['required', 'string', 'max:1000'],
            SystemSetting::KEY_HOMEPAGE_EYEBROW => ['nullable', 'string', 'max:80'],
            SystemSetting::KEY_HOMEPAGE_HEADING => ['required', 'string', 'max:160'],
            SystemSetting::KEY_HOMEPAGE_INTRO => ['required', 'string', 'max:700'],
            SystemSetting::KEY_FOOTER_INFORMATION => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            SystemSetting::KEY_SUPPORT_PHONE.'.regex' => 'Enter a valid phone number using numbers, spaces, brackets, dashes, or a leading plus sign.',
            SystemSetting::KEY_QUICK_EXIT_URL.'.regex' => 'The Quick Exit destination must begin with http:// or https://.',
            SystemSetting::KEY_CASE_REFERENCE_PREFIX.'.regex' => 'Use 2 to 10 uppercase letters, numbers, or dashes for the case reference prefix.',
        ];
    }
}

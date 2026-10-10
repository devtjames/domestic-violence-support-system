<?php

namespace App\Http\Requests\Admin;

use App\Models\SupportResource;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportResourceRequest extends FormRequest
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
        return [
            'organization_name' => ['required', 'string', 'max:160'],
            'resource_type' => ['required', 'string', Rule::in(SupportResource::resourceTypes())],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+().\-\s]{7,40}$/'],
            'email' => ['nullable', 'email', 'max:160'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:120'],
            'availability' => ['required', 'string', 'max:160'],
            'status' => ['required', 'string', Rule::in(SupportResource::statuses())],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid phone number using numbers, spaces, brackets, dashes, or a leading plus sign.',
        ];
    }
}

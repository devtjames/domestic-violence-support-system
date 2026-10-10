<?php

namespace App\Http\Requests\Admin;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactMessageRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::in(ContactMessage::statuses())],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

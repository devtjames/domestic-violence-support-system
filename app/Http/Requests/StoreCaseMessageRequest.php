<?php

namespace App\Http\Requests;

use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreCaseMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $supportCase = $this->route('support_case');

        if (! $user instanceof User || ! $supportCase instanceof SupportCase || $supportCase->is_anonymous) {
            return false;
        }

        if ($user->isRegisteredUser()) {
            return $supportCase->user_id === $user->id;
        }

        if ($user->isSupportOfficer()) {
            return $supportCase->assigned_officer_id === $user->id
                && $supportCase->user_id !== null;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:5000'],
        ];
    }
}

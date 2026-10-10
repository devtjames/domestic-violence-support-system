<?php

namespace App\Http\Requests;

use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreCaseNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $supportCase = $this->route('support_case');

        if (! $user instanceof User || ! $supportCase instanceof SupportCase) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSupportOfficer() && $supportCase->canBeManagedByOfficer($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'note' => ['required', 'string', 'min:3', 'max:3000'],
        ];
    }
}

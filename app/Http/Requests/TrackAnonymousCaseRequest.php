<?php

namespace App\Http\Requests;

use App\Models\SystemSetting;
use Illuminate\Foundation\Http\FormRequest;

class TrackAnonymousCaseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'case_reference' => strtoupper((string) $this->input('case_reference')),
            'access_code' => strtoupper((string) $this->input('access_code')),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $caseReferencePrefix = preg_quote(SystemSetting::caseReferencePrefix(), '/');

        return [
            'case_reference' => ['required', 'string', 'regex:/^'.$caseReferencePrefix.'-\d{4}-\d{6}$/'],
            'access_code' => ['required', 'string', 'alpha_num', 'between:6,16'],
        ];
    }
}

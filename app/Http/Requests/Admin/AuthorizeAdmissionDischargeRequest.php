<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AuthorizeAdmissionDischargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admissions.discharge') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'discharge_summary' => ['required', 'string'],
            'discharge_medications' => ['required', 'string'],
            'follow_up_plan' => ['required', 'string'],
            'billing_reviewed' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'billing_reviewed' => $this->boolean('billing_reviewed'),
        ]);
    }
}

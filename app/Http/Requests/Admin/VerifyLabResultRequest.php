<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyLabResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('lab.verify') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'value' => ['nullable', 'string', 'max:80'],
            'unit' => ['nullable', 'string', 'max:40'],
            'reference_range' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
            'findings' => ['nullable', 'string'],
            'is_critical' => ['sometimes', 'boolean'],
            'is_abnormal' => ['sometimes', 'boolean'],
            'signature_name' => ['nullable', 'string', 'max:120'],
            'signature_title' => ['nullable', 'string', 'max:120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_critical' => $this->boolean('is_critical'),
            'is_abnormal' => $this->boolean('is_abnormal'),
        ]);
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecordLabResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('lab.report') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'value' => ['required', 'string', 'max:80'],
            'unit' => ['nullable', 'string', 'max:40'],
            'reference_range' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
            'findings' => ['nullable', 'string'],
            'is_critical' => ['sometimes', 'boolean'],
            'is_abnormal' => ['sometimes', 'boolean'],
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

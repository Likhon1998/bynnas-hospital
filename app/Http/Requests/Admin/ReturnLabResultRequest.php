<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReturnLabResultRequest extends FormRequest
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
            'return_reason' => ['required', 'string', 'max:255'],
        ];
    }
}

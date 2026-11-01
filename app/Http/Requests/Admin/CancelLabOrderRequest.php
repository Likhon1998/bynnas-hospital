<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CancelLabOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('lab.order') ?? false)
            || ($this->user()?->can('lab.report') ?? false);
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'max:255'],
        ];
    }
}

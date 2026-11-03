<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CollectLabOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('lab.collect') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'sample_id' => ['nullable', 'string', 'max:40', Rule::unique('lab_orders', 'sample_id')],
            'barcode' => ['nullable', 'string', 'max:80'],
            'collected_at' => ['nullable', 'date'],
        ];
    }
}

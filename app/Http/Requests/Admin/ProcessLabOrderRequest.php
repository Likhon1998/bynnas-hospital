<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProcessLabOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('lab.report') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}

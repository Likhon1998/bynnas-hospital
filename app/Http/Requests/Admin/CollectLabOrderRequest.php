<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CollectLabOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('lab.collect') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}

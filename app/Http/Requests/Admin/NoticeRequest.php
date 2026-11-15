<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('content.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'published_on' => ['required', 'date'],
            'expires_on' => ['nullable', 'date', 'after_or_equal:published_on'],
            'is_urgent' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_urgent' => $this->boolean('is_urgent'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->integer('sort_order'),
            'expires_on' => $this->filled('expires_on') ? $this->input('expires_on') : null,
        ]);
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\Banner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BannerRequest extends FormRequest
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
        $imageRequired = ! $this->hasFile('image_upload') && ! $this->filled('image');

        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'string', 'max:2048'],
            'image_upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
            'cta_label' => ['nullable', 'string', 'max:255'],
            'cta_url' => ['nullable', 'string', 'max:2048'],
            'placement' => ['required', 'string', Rule::in(array_keys(Banner::placements()))],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Upload a banner image or paste an image URL.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->integer('sort_order'),
        ]);
    }
}

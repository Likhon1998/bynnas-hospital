<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('doctors.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'department_id' => ['required', 'exists:departments,id'],
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('doctors')->ignore($this->route('doctor'))],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('doctors')->ignore($this->route('doctor'))],
            'photo' => ['nullable', 'string', 'max:2048'],
            'designation' => ['required', 'string', 'max:255'],
            'qualifications' => ['nullable', 'string', 'max:500'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'registration_number' => ['nullable', 'string', 'max:100', Rule::unique('doctors')->ignore($this->route('doctor'))],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'biography' => ['nullable', 'string'],
            'is_featured' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug')
                ? Str::slug($this->string('slug')->toString())
                : Str::slug($this->string('name')->toString()),
            'user_id' => $this->filled('user_id') ? $this->integer('user_id') : null,
            'is_featured' => $this->boolean('is_featured'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->integer('sort_order'),
        ]);
    }
}

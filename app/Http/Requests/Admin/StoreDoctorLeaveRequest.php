<?php

namespace App\Http\Requests\Admin;

use App\Models\DoctorLeave;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDoctorLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('doctors.manage') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'type' => ['required', 'string', Rule::in(array_keys(DoctorLeave::TYPES))],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}

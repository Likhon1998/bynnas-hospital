<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CompleteAdmissionDischargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('admissions.admit') ?? false)
            || ($this->user()?->can('admissions.discharge') ?? false)
            || ($this->user()?->can('beds.operate') ?? false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}

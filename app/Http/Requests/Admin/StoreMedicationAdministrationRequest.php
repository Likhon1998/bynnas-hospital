<?php

namespace App\Http\Requests\Admin;

use App\Models\MedicationAdministration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicationAdministrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('nursing.meds') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'medication_name' => ['required', 'string', 'max:255'],
            'dose' => ['required', 'string', 'max:80'],
            'route' => ['required', 'string', Rule::in(array_keys(MedicationAdministration::ROUTES))],
            'status' => ['required', 'string', Rule::in(array_keys(MedicationAdministration::STATUSES))],
            'notes' => ['nullable', 'string'],
        ];
    }
}

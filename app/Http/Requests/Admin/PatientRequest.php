<?php

namespace App\Http\Requests\Admin;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('patients.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'secondary_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'nid_number' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'string', Rule::in(['male', 'female', 'other'])],
            'blood_group' => ['nullable', 'string', Rule::in(Patient::BLOOD_GROUPS)],
            'marital_status' => ['nullable', 'string', Rule::in(array_keys(Patient::MARITAL_STATUSES))],
            'occupation' => ['nullable', 'string', 'max:120'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'district' => ['nullable', 'string', 'max:120'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relation' => ['nullable', 'string', Rule::in(array_keys(Patient::EMERGENCY_RELATIONS))],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['required', 'boolean'],
            'care_flags' => ['nullable', 'array'],
            'care_flags.*' => ['string', Rule::in(array_keys(Patient::CARE_FLAGS))],
            'allergies' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_of_birth.required' => 'Date of birth is required for safe clinical care.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'gender.required' => 'Select the patient gender.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $careFlags = array_values(array_unique(array_filter((array) $this->input('care_flags', []))));

        if (filled($this->input('allergies')) && ! in_array('allergy_alert', $careFlags, true)) {
            $careFlags[] = 'allergy_alert';
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'care_flags' => $careFlags,
            'nid_number' => filled($this->input('nid_number')) ? trim((string) $this->input('nid_number')) : null,
            'secondary_phone' => filled($this->input('secondary_phone')) ? trim((string) $this->input('secondary_phone')) : null,
            'blood_group' => filled($this->input('blood_group')) ? $this->input('blood_group') : null,
            'marital_status' => filled($this->input('marital_status')) ? $this->input('marital_status') : null,
            'occupation' => filled($this->input('occupation')) ? trim((string) $this->input('occupation')) : null,
            'guardian_name' => filled($this->input('guardian_name')) ? trim((string) $this->input('guardian_name')) : null,
            'district' => filled($this->input('district')) ? trim((string) $this->input('district')) : null,
            'emergency_contact_relation' => filled($this->input('emergency_contact_relation')) ? $this->input('emergency_contact_relation') : null,
            'notes' => filled($this->input('notes')) ? trim((string) $this->input('notes')) : null,
        ]);
    }
}

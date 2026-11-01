<?php

namespace App\Http\Requests\Admin;

use App\Models\NursingObservation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordNursingObservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('nursing.chart') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        $hasVitals = $this->filled('blood_pressure')
            || $this->filled('temperature')
            || $this->filled('pulse')
            || $this->filled('oxygen_saturation')
            || $this->filled('pain_score')
            || $this->filled('weight');

        return [
            'observation_type' => ['required', 'string', Rule::in(array_keys(NursingObservation::TYPES))],
            'blood_pressure' => [$hasVitals ? 'required' : 'nullable', 'string', 'max:20', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'temperature' => [$hasVitals ? 'required' : 'nullable', 'numeric', 'min:30', 'max:45'],
            'pulse' => [$hasVitals ? 'required' : 'nullable', 'integer', 'min:30', 'max:220'],
            'oxygen_saturation' => [$hasVitals ? 'required' : 'nullable', 'integer', 'min:50', 'max:100'],
            'weight' => ['nullable', 'numeric', 'min:1', 'max:400'],
            'pain_score' => [$hasVitals ? 'required' : 'nullable', 'integer', 'min:0', 'max:10'],
            'notes' => [$hasVitals ? 'nullable' : 'required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'blood_pressure.regex' => 'Use a reading like 120/80.',
            'notes.required' => 'Add an observation note or a full set of vital signs.',
        ];
    }
}

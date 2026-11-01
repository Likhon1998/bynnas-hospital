<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecordVisitVitalsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('visits.triage') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'blood_pressure' => ['required', 'string', 'max:20', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'temperature' => ['required', 'numeric', 'min:30', 'max:45'],
            'pulse' => ['required', 'integer', 'min:30', 'max:220'],
            'oxygen_saturation' => ['required', 'integer', 'min:50', 'max:100'],
            'weight' => ['required', 'numeric', 'min:1', 'max:400'],
            'pain_score' => ['required', 'integer', 'min:0', 'max:10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'blood_pressure.regex' => 'Use a reading like 120/80.',
        ];
    }
}

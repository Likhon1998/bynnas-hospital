<?php

namespace App\Http\Requests\Admin;

use App\Models\Visit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecordVisitClinicalRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! ($this->user()?->can('visits.clinical') ?? false)) {
            return false;
        }

        $visit = $this->route('visit');

        if (! $visit instanceof Visit) {
            return false;
        }

        $user = $this->user();

        if ($user?->hasRole('doctor')) {
            $doctorId = $user->doctor?->id;

            if ($doctorId !== null && $visit->doctor_id !== null && (int) $visit->doctor_id !== (int) $doctorId) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        $complete = $this->boolean('complete');

        return [
            'symptoms' => [$complete ? 'required' : 'nullable', 'string'],
            'examination_notes' => ['nullable', 'string'],
            'diagnosis' => [$complete ? 'required' : 'nullable', 'string'],
            'treatment_plan' => [$complete ? 'required' : 'nullable', 'string'],
            'prescriptions' => ['nullable', 'string'],
            'follow_up_instructions' => ['nullable', 'string'],
            'complete' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'complete' => $this->boolean('complete'),
        ]);
    }
}

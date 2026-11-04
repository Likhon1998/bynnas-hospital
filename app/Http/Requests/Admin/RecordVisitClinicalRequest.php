<?php

namespace App\Http\Requests\Admin;

use App\Models\Visit;
use App\Support\ClinicalNoteOptions;
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
            'symptoms' => $this->composeClinicalField('symptoms'),
            'examination_notes' => $this->composeClinicalField('examination_notes'),
            'diagnosis' => $this->composeClinicalField('diagnosis'),
            'treatment_plan' => $this->composeClinicalField('treatment_plan'),
            'prescriptions' => $this->composePrescriptions(),
            'follow_up_instructions' => $this->composeClinicalField('follow_up_instructions'),
        ]);
    }

    private function composePrescriptions(): ?string
    {
        $lines = collect($this->input('rx_lines', []))
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($lines->isNotEmpty()) {
            return $lines->implode("\n");
        }

        return $this->composeClinicalField('prescriptions');
    }

    private function composeClinicalField(string $field): ?string
    {
        $allowed = ClinicalNoteOptions::for($field);
        $selected = collect($this->input($field.'_options', []))
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '' && ($allowed === [] || in_array($value, $allowed, true)))
            ->unique()
            ->values();

        $other = trim((string) $this->input($field.'_other', ''));
        $direct = $this->input($field);
        $hasPickerPayload = $this->has($field.'_options') || $this->has($field.'_other');

        if (! $hasPickerPayload && is_string($direct) && trim($direct) !== '') {
            return trim($direct);
        }

        $parts = $selected->all();

        if ($other !== '') {
            $parts[] = $other;
        }

        if ($parts === []) {
            return null;
        }

        return implode(ClinicalNoteOptions::joiner($field), $parts);
    }
}

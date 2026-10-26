<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\AuthorizesBedPermissions;
use App\Models\Bed;
use App\Support\BedLifecycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateBedRequest extends FormRequest
{
    use AuthorizesBedPermissions;

    public function authorize(): bool
    {
        return $this->canOperateBeds();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Bed $bed */
        $bed = $this->route('bed');
        $lifecycle = app(BedLifecycle::class);
        $allowed = $lifecycle->allowedNextStatuses($bed);
        $nextStatus = $this->string('status')->toString();
        $requiresInspectionNote = $bed->status === 'cleaning' && $nextStatus === 'dirty';

        return [
            'lock_version' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in($allowed)],
            'patient_id' => [
                Rule::requiredIf(fn (): bool => $nextStatus === 'occupied'),
                'nullable',
                'integer',
                Rule::exists('patients', 'id')->where(fn ($query) => $query->where('is_active', true)),
                Rule::unique('beds', 'patient_id')->ignore($bed->id),
            ],
            'notes' => [
                Rule::requiredIf($requiresInspectionNote),
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Bed $bed */
            $bed = $this->route('bed');
            $status = $this->string('status')->toString();
            $lifecycle = app(BedLifecycle::class);

            if ($status !== '' && ! $lifecycle->canTransition($bed, $status)) {
                $validator->errors()->add(
                    'status',
                    "Bed {$bed->bed_code} cannot move from {$bed->status} to {$status}."
                );
            }

            if ($status === 'occupied') {
                foreach ($bed->admissionBlockers($this->integer('patient_id') ?: null) as $blocker) {
                    $validator->errors()->add('patient_id', $blocker);
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lock_version.required' => 'Refresh the board — this bed form is missing a lock version.',
            'status.in' => 'That status change is not allowed for this bed right now.',
            'patient_id.required' => 'Select a patient before occupying the bed.',
            'patient_id.unique' => 'That patient is already assigned to another bed.',
            'patient_id.exists' => 'Choose an active patient from the hospital census.',
            'notes.required' => 'Provide a reason when failing a cleaning inspection.',
        ];
    }
}

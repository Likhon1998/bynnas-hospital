<?php

namespace App\Http\Requests\Admin;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Visit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admissions.admit') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'bed_id' => ['required', 'integer', 'exists:beds,id'],
            'source' => ['required', 'string', Rule::in(array_keys(Admission::SOURCES))],
            'admission_reason' => ['required', 'string', 'max:500'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'visit_id' => ['nullable', 'integer', 'exists:visits,id'],
            'attending_doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $bed = Bed::query()->with('ward')->find($this->integer('bed_id'));
                $patientId = $this->integer('patient_id');

                if ($bed === null || ! $bed->isAdmittable($patientId)) {
                    $validator->errors()->add(
                        'bed_id',
                        $bed?->admissionBlockers($patientId)[0] ?? 'Choose a bed that is ready to admit.'
                    );
                }

                if ($this->filled('appointment_id')) {
                    $appointment = Appointment::query()->find($this->integer('appointment_id'));

                    if ($appointment === null || (int) $appointment->patient_id !== $patientId) {
                        $validator->errors()->add('appointment_id', 'That appointment does not belong to this patient.');
                    }
                }

                if ($this->filled('visit_id')) {
                    $visit = Visit::query()->find($this->integer('visit_id'));

                    if ($visit === null || (int) $visit->patient_id !== $patientId) {
                        $validator->errors()->add('visit_id', 'That encounter does not belong to this patient.');
                    }
                }

                if ($this->filled('attending_doctor_id')) {
                    $doctor = Doctor::query()->find($this->integer('attending_doctor_id'));

                    if ($doctor === null || ! $doctor->is_active) {
                        $validator->errors()->add('attending_doctor_id', 'Choose an active attending doctor.');
                    }
                }
            },
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\Doctor;
use App\Support\AppointmentDesk;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointments.manage') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'reason' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['pending', 'confirmed'])],
            'source' => ['required', 'string', Rule::in(['admin', 'emergency'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('start_time')) {
            $this->merge([
                'start_time' => Carbon::parse($this->string('start_time')->toString())->format('H:i'),
            ]);
        }
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

                $doctor = Doctor::query()->find($this->integer('doctor_id'));

                if ($doctor === null || ! $doctor->is_active || $doctor->department_id === null) {
                    $validator->errors()->add('doctor_id', 'Choose an active doctor with a department.');

                    return;
                }

                $doctor->loadMissing(['doctorSchedules', 'doctorLeaves']);

                $reason = app(AppointmentDesk::class)->unavailableReason(
                    $doctor,
                    $this->string('appointment_date')->toString(),
                    $this->string('start_time')->toString(),
                    $this->string('source')->toString(),
                );

                if ($reason !== null) {
                    $validator->errors()->add('start_time', $reason);
                }
            },
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\Appointment;
use App\Support\AppointmentDesk;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AppointmentStatusRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::in(array_keys(Appointment::STATUSES))],
            'notes' => ['nullable', 'string'],
            'reason' => ['nullable', 'string', 'max:500'],
            'appointment_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i', 'required_with:appointment_date'],
            'cancellation_reason' => ['nullable', 'string', 'max:255', 'required_if:status,cancelled'],
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

                $appointment = $this->route('appointment');

                if (! $appointment instanceof Appointment) {
                    return;
                }

                $status = $this->string('status')->toString();

                if ($status === 'cancelled') {
                    return;
                }

                $date = $this->filled('appointment_date')
                    ? $this->string('appointment_date')->toString()
                    : $appointment->appointment_date?->toDateString();
                $start = $this->filled('start_time')
                    ? $this->string('start_time')->toString()
                    : substr((string) $appointment->start_time, 0, 5);

                if ($date === null || $start === '') {
                    return;
                }

                $willHold = $status !== 'pending' || $appointment->source !== 'web';

                if ($willHold && app(AppointmentDesk::class)->slotTaken(
                    (int) $appointment->doctor_id,
                    $date,
                    $start,
                    $appointment->id,
                )) {
                    $validator->errors()->add(
                        $this->filled('start_time') ? 'start_time' : 'status',
                        'That slot is already booked for this doctor.',
                    );
                }
            },
        ];
    }
}

<?php

namespace App\Http\Requests\Web;

use App\Models\Doctor;
use App\Models\Service;
use App\Support\AppointmentDesk;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'service' => [
                'required',
                'string',
                Rule::exists('services', 'slug')->where('is_active', true),
            ],
            'date' => ['required', 'date', 'after_or_equal:today', 'before:'.now()->addMonths(3)->toDateString()],
            'doctor' => ['required', 'integer', Rule::exists('doctors', 'id')->where('is_active', true)],
            'time' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'doctor.required' => 'Choose a doctor first.',
            'date.required' => 'Pick a visit day from the clinic schedule.',
            'time.required' => 'Pick a free time from the clinic schedule.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('time')) {
            $this->merge([
                'time' => substr($this->string('time')->toString(), 0, 5),
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
                if ($this->filled('doctor') && $this->filled('service')) {
                    $doctor = Doctor::query()->find($this->integer('doctor'));
                    $service = Service::query()->where('slug', $this->string('service')->toString())->first();

                    if (
                        $doctor !== null
                        && $service !== null
                        && $doctor->department_id
                        && $service->department_id
                        && (int) $doctor->department_id !== (int) $service->department_id
                    ) {
                        $validator->errors()->add('service', 'Choose a service this doctor provides.');
                    }
                }

                if ($validator->errors()->isNotEmpty() || ! $this->filled('doctor') || ! $this->filled('date') || ! $this->filled('time')) {
                    return;
                }

                $doctor = Doctor::query()
                    ->with(['doctorSchedules', 'doctorLeaves'])
                    ->find($this->integer('doctor'));

                if ($doctor === null) {
                    $validator->errors()->add('doctor', 'Choose an active doctor.');

                    return;
                }

                $reason = app(AppointmentDesk::class)->unavailableReason(
                    $doctor,
                    $this->string('date')->toString(),
                    $this->string('time')->toString(),
                    'web',
                );

                if ($reason !== null) {
                    $validator->errors()->add('time', $reason);
                }
            },
        ];
    }
}

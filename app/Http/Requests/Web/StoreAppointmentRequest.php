<?php

namespace App\Http\Requests\Web;

use App\Models\Doctor;
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
            'doctor' => ['nullable', 'integer', Rule::exists('doctors', 'id')->where('is_active', true)],
            'time' => ['nullable', 'date_format:H:i'],
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
                if ($validator->errors()->isNotEmpty() || ! $this->filled('doctor') || ! $this->filled('time')) {
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

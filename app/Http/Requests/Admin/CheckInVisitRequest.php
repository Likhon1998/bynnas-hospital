<?php

namespace App\Http\Requests\Admin;

use App\Models\Doctor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckInVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('visits.checkin') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'chief_complaint' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->filled('doctor_id')) {
                    return;
                }

                $doctor = Doctor::query()->find($this->integer('doctor_id'));

                if ($doctor === null || ! $doctor->is_active) {
                    $validator->errors()->add('doctor_id', 'Choose an active doctor.');
                }
            },
        ];
    }
}

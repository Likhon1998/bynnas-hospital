<?php

namespace App\Http\Requests\Admin;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('billing.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'bill_date' => ['required', 'date'],
            'discount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'action' => ['nullable', Rule::in(['draft', 'issue'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', Rule::in(['custom', 'service', 'package', 'consultation'])],
            'items.*.item_id' => ['nullable', 'integer'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'appointment_id' => $this->filled('appointment_id') ? $this->integer('appointment_id') : null,
            'discount' => $this->filled('discount') ? $this->input('discount') : 0,
        ]);
    }

    /**
     * @return list<\Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['patient_id', 'appointment_id']) || ! $this->filled('appointment_id')) {
                    return;
                }

                $appointment = Appointment::query()->find($this->integer('appointment_id'));

                if ($appointment && $appointment->patient_id !== $this->integer('patient_id')) {
                    $validator->errors()->add('appointment_id', 'The selected appointment does not belong to this patient.');
                }
            },
        ];
    }
}

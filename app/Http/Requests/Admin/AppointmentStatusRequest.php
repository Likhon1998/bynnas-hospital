<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('appointments.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['pending', 'confirmed', 'completed', 'cancelled', 'no_show'])],
            'notes' => ['nullable', 'string'],
            'cancellation_reason' => ['nullable', 'string', 'max:255', 'required_if:status,cancelled'],
        ];
    }
}

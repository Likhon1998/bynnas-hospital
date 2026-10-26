<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\AuthorizesBedPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReserveBedRequest extends FormRequest
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
        return [
            'lock_version' => ['required', 'integer', 'min:0'],
            'patient_id' => [
                'required',
                'integer',
                Rule::exists('patients', 'id')->where(fn ($query) => $query->where('is_active', true)),
                Rule::unique('beds', 'patient_id'),
                Rule::unique('beds', 'reserved_patient_id'),
            ],
            'reserved_until' => ['nullable', 'date', 'after:now'],
            'reservation_note' => ['nullable', 'string', 'max:255'],
        ];
    }
}

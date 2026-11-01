<?php

namespace App\Http\Requests\Admin;

use App\Models\NursingHandover;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNursingHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('nursing.handover') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'shift' => ['required', 'string', Rule::in(array_keys(NursingHandover::SHIFTS))],
            'incoming_shift' => ['required', 'string', 'different:shift', Rule::in(array_keys(NursingHandover::SHIFTS))],
            'summary' => ['required', 'string'],
            'outstanding_work' => ['nullable', 'string'],
        ];
    }
}

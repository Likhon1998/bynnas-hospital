<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
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
            'date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}

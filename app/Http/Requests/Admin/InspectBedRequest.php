<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\AuthorizesBedPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InspectBedRequest extends FormRequest
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
            'result' => ['required', 'string', Rule::in(['pass', 'fail'])],
            'notes' => [
                Rule::requiredIf($this->string('result')->toString() === 'fail'),
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'notes.required' => 'Provide a reason when failing a cleaning inspection.',
        ];
    }
}

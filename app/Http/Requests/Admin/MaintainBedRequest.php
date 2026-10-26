<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\AuthorizesBedPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintainBedRequest extends FormRequest
{
    use AuthorizesBedPermissions;

    public function authorize(): bool
    {
        return $this->canMaintainBeds();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'lock_version' => ['required', 'integer', 'min:0'],
            'action' => ['required', 'string', Rule::in(['start', 'end'])],
            'reason' => [
                Rule::requiredIf($this->string('action')->toString() === 'start'),
                'nullable',
                'string',
                'max:255',
            ],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}

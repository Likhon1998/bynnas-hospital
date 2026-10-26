<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\AuthorizesBedPermissions;
use App\Models\Bed;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferBedRequest extends FormRequest
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
        /** @var Bed $bed */
        $bed = $this->route('bed');

        return [
            'lock_version' => ['required', 'integer', 'min:0'],
            'to_lock_version' => ['required', 'integer', 'min:0'],
            'to_bed_id' => [
                'required',
                'integer',
                Rule::notIn([$bed->id]),
                Rule::exists('beds', 'id'),
            ],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\Admission;
use App\Models\Bed;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TransferAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('beds.operate') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'to_bed_id' => ['required', 'integer', 'exists:beds,id'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Admission $admission */
                $admission = $this->route('admission');
                $toBed = Bed::query()->find($this->integer('to_bed_id'));

                if ($toBed === null) {
                    return;
                }

                if ($admission->bed_id !== null && $toBed->id === $admission->bed_id) {
                    $validator->errors()->add('to_bed_id', 'Choose a different destination bed.');
                }

                if (! $toBed->isAdmittable($admission->patient_id)) {
                    $validator->errors()->add(
                        'to_bed_id',
                        $toBed->admissionBlockers($admission->patient_id)[0] ?? 'Destination bed is not ready.'
                    );
                }
            },
        ];
    }
}

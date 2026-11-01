<?php

namespace App\Http\Requests\Admin;

use App\Models\LabTest;
use App\Models\Visit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLabOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('lab.order') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'visit_id' => ['required', 'integer', 'exists:visits,id'],
            'lab_test_id' => ['required', 'integer', 'exists:lab_tests,id'],
            'clinical_notes' => ['nullable', 'string', 'max:500'],
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

                $visit = Visit::query()->find($this->integer('visit_id'));

                if ($visit === null || $visit->status === 'cancelled') {
                    $validator->errors()->add('visit_id', 'Choose an active clinical encounter.');
                }

                $test = LabTest::query()->find($this->integer('lab_test_id'));

                if ($test === null || ! $test->is_active) {
                    $validator->errors()->add('lab_test_id', 'Choose an active laboratory test.');
                }
            },
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\NursingTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNursingTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('nursing.chart') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'task_type' => ['required', 'string', Rule::in(array_keys(NursingTask::TYPES))],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
        ];
    }
}

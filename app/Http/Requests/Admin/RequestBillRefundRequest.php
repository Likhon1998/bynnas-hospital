<?php

namespace App\Http\Requests\Admin;

use App\Models\Bill;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RequestBillRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('billing.manage') ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return list<\Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $bill = $this->route('bill');

                if (! $bill instanceof Bill || $validator->errors()->isNotEmpty()) {
                    return;
                }

                if (! $bill->canRequestRefund()) {
                    $validator->errors()->add('amount', 'Only bills with collected payments can request a refund.');

                    return;
                }

                if (round((float) $this->input('amount'), 2) > (float) $bill->paid_amount) {
                    $validator->errors()->add('amount', 'Refund cannot exceed the amount already collected.');
                }
            },
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\Bill;
use App\Models\BillPayment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CollectBillPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! ($this->user()?->can('billing.manage') ?? false)) {
            return false;
        }

        $bill = $this->route('bill');

        return $bill instanceof Bill && $bill->canCollectPayment();
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(array_keys(BillPayment::METHODS))],
            'reference' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:255'],
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

                $amount = round((float) $this->input('amount'), 2);

                if ($amount > $bill->balanceDue()) {
                    $validator->errors()->add('amount', 'Amount cannot exceed the outstanding balance of '.number_format($bill->balanceDue(), 2).'.');
                }

                if (in_array($this->input('method'), ['bkash', 'nagad', 'card'], true) && blank($this->input('reference'))) {
                    $validator->errors()->add('reference', 'Add a transaction / card reference for this payment method.');
                }
            },
        ];
    }
}

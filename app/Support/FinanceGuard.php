<?php

namespace App\Support;

use App\Models\Bill;
use App\Models\FinanceApproval;
use App\Models\User;

class FinanceGuard
{
    public const HIGH_VALUE_DISCOUNT_AMOUNT = 500.0;

    public const HIGH_VALUE_DISCOUNT_PERCENT = 10.0;

    public function canApplyDiscount(?User $user, float $discount, float $subtotal): bool
    {
        if ($discount <= 0) {
            return true;
        }

        if ($user?->can('finance.approve')) {
            return true;
        }

        return ! $this->isHighValueDiscount($discount, $subtotal);
    }

    public function isHighValueDiscount(float $discount, float $subtotal): bool
    {
        if ($discount >= self::HIGH_VALUE_DISCOUNT_AMOUNT) {
            return true;
        }

        if ($subtotal <= 0) {
            return false;
        }

        return ($discount / $subtotal) * 100 >= self::HIGH_VALUE_DISCOUNT_PERCENT;
    }

    public function requestDiscountApproval(
        User $actor,
        float $amount,
        string $reason,
        ?Bill $bill = null,
        ?string $notes = null,
    ): FinanceApproval {
        return FinanceApproval::query()->create([
            'type' => FinanceApproval::TYPE_DISCOUNT,
            'status' => FinanceApproval::STATUS_PENDING,
            'bill_id' => $bill?->id,
            'amount' => $amount,
            'reason' => $reason,
            'notes' => $notes,
            'requested_by' => $actor->id,
        ]);
    }

    public function requestRefundApproval(
        User $actor,
        float $amount,
        string $reason,
        ?Bill $bill = null,
        ?string $notes = null,
    ): FinanceApproval {
        return FinanceApproval::query()->create([
            'type' => FinanceApproval::TYPE_REFUND,
            'status' => FinanceApproval::STATUS_PENDING,
            'bill_id' => $bill?->id,
            'amount' => $amount,
            'reason' => $reason,
            'notes' => $notes,
            'requested_by' => $actor->id,
        ]);
    }
}

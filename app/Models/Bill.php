<?php

namespace App\Models;

use Database\Factories\BillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'bill_no',
    'report_access_code',
    'patient_id',
    'appointment_id',
    'issued_by',
    'bill_date',
    'status',
    'subtotal',
    'discount',
    'total',
    'paid_amount',
    'notes',
    'issued_at',
    'paid_at',
    'cancelled_at',
    'cancellation_reason',
])]
class Bill extends Model
{
    /** @use HasFactory<BillFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bill_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Bill>  $query
     */
    public function scopeUnpaid(Builder $query): void
    {
        $query->whereIn('status', ['draft', 'issued']);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function billItems(): HasMany
    {
        return $this->hasMany(BillItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class)->orderByDesc('collected_at')->orderByDesc('id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isEditable(): bool
    {
        return $this->isDraft();
    }

    public function canIssue(): bool
    {
        return $this->isDraft() && $this->billItems()->exists();
    }

    public function canMarkPaid(): bool
    {
        return $this->canCollectPayment();
    }

    public function canCollectPayment(): bool
    {
        return $this->status === 'issued' && $this->balanceDue() > 0;
    }

    public function canRequestRefund(): bool
    {
        return in_array($this->status, ['paid', 'issued'], true)
            && (float) $this->paid_amount > 0;
    }

    public function balanceDue(): float
    {
        return max(0, round((float) $this->total - (float) $this->paid_amount, 2));
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['draft', 'issued'], true)
            && (float) $this->paid_amount <= 0;
    }

    public static function nextBillNumber(): string
    {
        $prefix = 'BILL-'.now()->format('Ymd').'-';

        $last = static::query()
            ->where('bill_no', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('bill_no')
            ->value('bill_no');

        $sequence = $last ? ((int) substr((string) $last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public static function nextReportAccessCode(): string
    {
        do {
            $code = (string) random_int(10000000, 99999999);
        } while (static::query()->where('report_access_code', $code)->exists());

        return $code;
    }

    public function ensureReportAccessCode(): string
    {
        if (filled($this->report_access_code)) {
            return (string) $this->report_access_code;
        }

        $code = static::nextReportAccessCode();

        $this->forceFill(['report_access_code' => $code])->save();

        return $code;
    }

    public function hasLabCharges(): bool
    {
        return $this->billItems()->where('item_type', 'lab')->exists();
    }

    /**
     * @return Collection<int, LabOrder>
     */
    public function publishedLabOrders()
    {
        $orderIds = $this->billItems()
            ->where('item_type', 'lab')
            ->whereNotNull('item_id')
            ->pluck('item_id')
            ->all();

        if ($orderIds === []) {
            return LabOrder::query()->whereRaw('0 = 1')->get();
        }

        return LabOrder::query()
            ->with(['test', 'result', 'patient'])
            ->whereIn('id', $orderIds)
            ->where('status', 'completed')
            ->whereHas('result', fn ($result) => $result->whereNotNull('published_at')->whereNotNull('verified_at'))
            ->orderByDesc('completed_at')
            ->get();
    }

    /**
     * @param  list<array{item_type?: string, item_id?: int|null, description: string, quantity: int, unit_price: float|int|string}>  $items
     */
    public function syncItems(array $items): void
    {
        $this->billItems()->where('item_type', '!=', 'lab')->delete();

        $nextSort = ((int) $this->billItems()->max('sort_order')) + 1;

        foreach (array_values($items) as $item) {
            if (($item['item_type'] ?? 'custom') === 'lab') {
                continue;
            }

            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $unitPrice = round((float) ($item['unit_price'] ?? 0), 2);

            $this->billItems()->create([
                'item_type' => $item['item_type'] ?? 'custom',
                'item_id' => $item['item_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($quantity * $unitPrice, 2),
                'sort_order' => $nextSort,
            ]);

            $nextSort++;
        }

        $this->recalculateTotals();
    }

    public function appendUniqueLine(
        string $itemType,
        int $itemId,
        string $description,
        int $quantity,
        float $unitPrice,
    ): BillItem {
        $existing = $this->billItems()
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->first();

        if ($existing) {
            return $existing;
        }

        $line = $this->billItems()->create([
            'item_type' => $itemType,
            'item_id' => $itemId,
            'description' => $description,
            'quantity' => max(1, $quantity),
            'unit_price' => round($unitPrice, 2),
            'line_total' => round(max(1, $quantity) * $unitPrice, 2),
            'sort_order' => ((int) $this->billItems()->max('sort_order')) + 1,
        ]);

        $this->recalculateTotals();

        return $line;
    }

    public function recalculateTotals(): void
    {
        $subtotal = round((float) $this->billItems()->sum('line_total'), 2);
        $discount = min((float) $this->discount, $subtotal);
        $total = max(0, round($subtotal - $discount, 2));

        $this->forceFill([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
        ])->save();
    }

    public static function createFromReception(array $attributes, array $items): self
    {
        return DB::transaction(function () use ($attributes, $items): self {
            $bill = static::query()->create([
                ...$attributes,
                'bill_no' => static::nextBillNumber(),
                'subtotal' => 0,
                'total' => 0,
                'paid_amount' => 0,
            ]);

            $bill->syncItems($items);

            return $bill->refresh()->load('billItems');
        });
    }
}

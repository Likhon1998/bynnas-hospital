<?php

namespace App\Models;

use Database\Factories\BillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'bill_no',
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
        return $this->status === 'issued';
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['draft', 'issued'], true);
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

    /**
     * @param  list<array{item_type?: string, item_id?: int|null, description: string, quantity: int, unit_price: float|int|string}>  $items
     */
    public function syncItems(array $items): void
    {
        $this->billItems()->delete();

        $subtotal = 0;

        foreach (array_values($items) as $index => $item) {
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $unitPrice = round((float) ($item['unit_price'] ?? 0), 2);
            $lineTotal = round($quantity * $unitPrice, 2);
            $subtotal += $lineTotal;

            $this->billItems()->create([
                'item_type' => $item['item_type'] ?? 'custom',
                'item_id' => $item['item_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'sort_order' => $index + 1,
            ]);
        }

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

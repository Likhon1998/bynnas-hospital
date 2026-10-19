<?php

namespace App\Models;

use Database\Factories\BillItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bill_id',
    'item_type',
    'item_id',
    'description',
    'quantity',
    'unit_price',
    'line_total',
    'sort_order',
])]
class BillItem extends Model
{
    /** @use HasFactory<BillItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'sort_order' => 'integer',
            'item_id' => 'integer',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}

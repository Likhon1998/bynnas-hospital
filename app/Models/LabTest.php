<?php

namespace App\Models;

use Database\Factories\LabTestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'name',
    'category',
    'specimen',
    'unit',
    'reference_range',
    'critical_low',
    'critical_high',
    'price',
    'is_active',
])]
class LabTest extends Model
{
    /** @use HasFactory<LabTestFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const CATEGORIES = [
        'hematology' => 'Hematology',
        'chemistry' => 'Chemistry',
        'cardiac' => 'Cardiac',
        'endocrine' => 'Endocrine',
        'serology' => 'Serology',
        'urine' => 'Urine',
        'radiology' => 'Radiology',
        'imaging' => 'Imaging',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'critical_low' => 'decimal:3',
            'critical_high' => 'decimal:3',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
    }

    public function isCriticalValue(?float $numeric): bool
    {
        if ($numeric === null) {
            return false;
        }

        if ($this->critical_low !== null && $numeric <= (float) $this->critical_low) {
            return true;
        }

        return $this->critical_high !== null && $numeric >= (float) $this->critical_high;
    }

    /**
     * @param  Builder<LabTest>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(LabOrder::class);
    }
}

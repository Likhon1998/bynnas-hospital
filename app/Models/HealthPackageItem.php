<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'health_package_id',
    'title',
    'sort_order',
])]
class HealthPackageItem extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function healthPackage(): BelongsTo
    {
        return $this->belongsTo(HealthPackage::class);
    }
}

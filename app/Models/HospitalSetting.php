<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

#[Fillable([
    'key',
    'value',
    'group',
])]
class HospitalSetting extends Model
{
    /**
     * @return Collection<string, string|null>
     */
    public static function map(): Collection
    {
        return static::query()->pluck('value', 'key');
    }
}

<?php

namespace App\Http\Requests\Concerns;

trait AuthorizesBedPermissions
{
    protected function canViewBeds(): bool
    {
        return $this->user()?->can('beds.view') ?? false;
    }

    protected function canOperateBeds(): bool
    {
        return $this->user()?->can('beds.operate') ?? false;
    }

    protected function canMaintainBeds(): bool
    {
        return $this->user()?->can('beds.maintain') ?? false;
    }
}

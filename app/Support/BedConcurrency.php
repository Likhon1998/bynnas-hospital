<?php

namespace App\Support;

use App\Exceptions\ConcurrentBedUpdateException;
use App\Models\Bed;

class BedConcurrency
{
    /**
     * Optimistic lock claim after a row lock is held.
     * Floor UI must send the version the operator loaded; background jobs may force.
     */
    public function claim(Bed $bed, ?int $expectedVersion, bool $force = false): void
    {
        if (! $force) {
            if ($expectedVersion === null || (int) $bed->lock_version !== (int) $expectedVersion) {
                throw new ConcurrentBedUpdateException(
                    "Bed {$bed->bed_code} was changed by another user. Refresh the board and try again."
                );
            }
        }

        $bed->lock_version = (int) $bed->lock_version + 1;
    }
}

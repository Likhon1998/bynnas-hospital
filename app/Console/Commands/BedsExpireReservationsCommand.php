<?php

namespace App\Console\Commands;

use App\Models\Bed;
use App\Support\BedConcurrency;
use App\Support\BedLifecycle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BedsExpireReservationsCommand extends Command
{
    protected $signature = 'beds:expire-reservations';

    protected $description = 'Clear expired bed reservations and write audit events';

    public function handle(BedLifecycle $lifecycle, BedConcurrency $concurrency): int
    {
        $expiredIds = Bed::query()
            ->whereNotNull('reserved_patient_id')
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<=', now())
            ->orderBy('id')
            ->pluck('id');

        $cleared = 0;

        foreach ($expiredIds as $bedId) {
            DB::transaction(function () use ($bedId, $lifecycle, $concurrency, &$cleared): void {
                $bed = Bed::query()->lockForUpdate()->find($bedId);

                if ($bed === null || ! $bed->reservationIsExpired()) {
                    return;
                }

                $concurrency->claim($bed, expectedVersion: null, force: true);
                $lifecycle->clearExpiredReservation($bed);
                $cleared++;
            });
        }

        $this->info("Cleared {$cleared} expired bed reservation(s).");

        return self::SUCCESS;
    }
}

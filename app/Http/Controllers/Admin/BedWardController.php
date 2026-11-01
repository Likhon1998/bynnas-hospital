<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ConcurrentBedUpdateException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InspectBedRequest;
use App\Http\Requests\Admin\MaintainBedRequest;
use App\Http\Requests\Admin\ReserveBedRequest;
use App\Http\Requests\Admin\TransferBedRequest;
use App\Http\Requests\Admin\UpdateBedRequest;
use App\Models\Bed;
use App\Models\Patient;
use App\Support\AdmissionDesk;
use App\Support\BedLifecycle;
use App\Support\BedOperations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class BedWardController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $search = $request->string('q')->toString();

        $bedsQuery = Bed::query()
            ->with(['ward', 'patient', 'reservedPatient', 'currentAdmission'])
            ->when($type !== '', fn ($query) => $query->whereHas('ward', fn ($ward) => $ward->where('type', $type)))
            ->when($status === 'turnaround', fn ($query) => $query->whereIn('status', ['dirty', 'cleaning']))
            ->when(
                $status !== '' && $status !== 'turnaround',
                fn ($query) => $query->where('status', $status)
            )
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('bed_code', 'like', '%'.$search.'%')
                        ->orWhereHas('patient', fn ($patient) => $patient->where('full_name', 'like', '%'.$search.'%'))
                        ->orWhereHas('reservedPatient', fn ($patient) => $patient->where('full_name', 'like', '%'.$search.'%'));
                });
            })
            ->orderByDesc('is_maintenance')
            ->orderByRaw("CASE status WHEN 'dirty' THEN 0 WHEN 'cleaning' THEN 1 WHEN 'occupied' THEN 2 ELSE 3 END")
            ->orderBy('bed_code');

        $allBeds = Bed::query()->with(['ward', 'patient', 'reservedPatient'])->get();
        $totalBeds = $allBeds->count();
        $occupiedBeds = $allBeds->where('status', 'occupied')->count();
        $availableBeds = $allBeds->where('status', 'available')->count();
        $dirtyBeds = $allBeds->where('status', 'dirty')->count();
        $cleaningBeds = $allBeds->where('status', 'cleaning')->count();
        $turnaroundBeds = $dirtyBeds + $cleaningBeds;
        $admittableBeds = $allBeds->filter(fn (Bed $bed): bool => $bed->isAdmittable())->count();
        $reservedBeds = $allBeds->filter(fn (Bed $bed): bool => $bed->hasActiveReservation())->count();
        $maintenanceBeds = $allBeds->where('is_maintenance', true)->count();

        $occupiedWithStay = $allBeds
            ->where('status', 'occupied')
            ->filter(fn (Bed $bed): bool => $bed->admitted_at !== null);

        $alosHours = $occupiedWithStay->avg(
            fn (Bed $bed): float => (float) $bed->admitted_at?->diffInHours(now())
        );

        $typeCounts = [
            'all' => $totalBeds,
            'icu' => $allBeds->filter(fn (Bed $bed): bool => $bed->ward?->type === 'icu')->count(),
            'general' => $allBeds->filter(fn (Bed $bed): bool => $bed->ward?->type === 'general')->count(),
            'nicu' => $allBeds->filter(fn (Bed $bed): bool => $bed->ward?->type === 'nicu')->count(),
            'isolation' => $allBeds->filter(fn (Bed $bed): bool => $bed->ward?->type === 'isolation')->count(),
        ];

        $admitPatients = Patient::query()
            ->active()
            ->whereDoesntHave('bed')
            ->orderBy('full_name')
            ->limit(200)
            ->get(['id', 'full_name', 'phone']);

        $transferTargets = Bed::query()
            ->with('ward')
            ->admittable()
            ->orderBy('bed_code')
            ->get(['id', 'bed_code', 'ward_id', 'reserved_patient_id', 'lock_version']);

        $icuCensus = $allBeds
            ->filter(fn (Bed $bed): bool => $bed->ward?->type === 'icu' && $bed->status === 'occupied' && $bed->patient !== null)
            ->sortBy(fn (Bed $bed): string => $bed->bed_code)
            ->values();

        $lifecycle = app(BedLifecycle::class);

        return view('admin.beds.index', [
            'beds' => $bedsQuery->paginate(12)->withQueryString(),
            'type' => $type,
            'status' => $status,
            'search' => $search,
            'icuCensus' => $icuCensus,
            'typeCounts' => $typeCounts,
            'admitPatients' => $admitPatients,
            'transferTargets' => $transferTargets,
            'lifecycle' => $lifecycle,
            'statusCounts' => [
                'all' => $totalBeds,
                'available' => $availableBeds,
                'occupied' => $occupiedBeds,
                'dirty' => $dirtyBeds,
                'cleaning' => $cleaningBeds,
                'turnaround' => $turnaroundBeds,
            ],
            'kpis' => [
                [
                    'label' => 'Census beds',
                    'value' => (string) $totalBeds,
                    'percent' => (int) round(($occupiedBeds / max($totalBeds, 1)) * 100),
                    'tone' => 'navy',
                    'chart' => 'bar',
                    'meta' => $occupiedBeds.' occupied · '.$availableBeds.' available',
                    'trend' => $occupiedWithStay->isEmpty()
                        ? 'ALOS unavailable'
                        : 'ALOS '.number_format(max(0.1, ($alosHours ?: 0) / 24), 1).' days',
                    'trendUp' => $admittableBeds > 0,
                    'href' => route('admin.beds.index'),
                ],
                [
                    'label' => 'Occupied',
                    'value' => (string) $occupiedBeds,
                    'percent' => (int) round(($occupiedBeds / max($totalBeds, 1)) * 100),
                    'tone' => 'blue',
                    'chart' => 'donut',
                    'meta' => 'of '.$totalBeds.' beds',
                    'trend' => (int) round(($occupiedBeds / max($totalBeds, 1)) * 100).'% occupancy',
                    'trendUp' => $occupiedBeds > 0,
                    'href' => route('admin.beds.index', ['status' => 'occupied']),
                ],
                [
                    'label' => 'Ready to admit',
                    'value' => (string) $admittableBeds,
                    'percent' => (int) round(($admittableBeds / max($totalBeds, 1)) * 100),
                    'tone' => 'green',
                    'chart' => 'bar',
                    'meta' => $reservedBeds.' reserved · '.$maintenanceBeds.' maintenance',
                    'trend' => $admittableBeds === 0 ? 'No eligible clean beds' : $admittableBeds.' eligible now',
                    'trendUp' => $admittableBeds > 0,
                    'href' => route('admin.beds.index', ['status' => 'available']),
                ],
                [
                    'label' => 'Turnaround queue',
                    'value' => (string) $turnaroundBeds,
                    'percent' => (int) round(($turnaroundBeds / max($totalBeds, 1)) * 100),
                    'tone' => 'orange',
                    'chart' => 'bar',
                    'meta' => $dirtyBeds.' dirty · '.$cleaningBeds.' cleaning',
                    'trend' => $turnaroundBeds === 0 ? 'No housekeeping backlog' : 'Needs housekeeping',
                    'trendUp' => $turnaroundBeds === 0,
                    'href' => route('admin.beds.index', ['status' => 'turnaround']),
                ],
            ],
        ]);
    }

    public function update(UpdateBedRequest $request, Bed $bed, BedLifecycle $lifecycle, AdmissionDesk $desk): RedirectResponse
    {
        $status = $request->string('status')->toString();
        $patientId = $request->integer('patient_id') ?: null;
        $notes = $request->string('admission_reason')->toString()
            ?: $request->string('notes')->toString()
            ?: null;
        $previousStatus = $bed->status;
        $dischargedPatientId = $bed->patient_id;

        $result = $this->runBedMutation(
            $bed,
            $request->integer('lock_version'),
            function (Bed $freshBed, int $lockVersion) use ($lifecycle, $status, $patientId, $request, $notes): void {
                $lifecycle->apply(
                    $freshBed,
                    $status,
                    $patientId,
                    $request->user(),
                    $notes,
                    $lockVersion,
                );
            },
        );

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        $actor = $request->user();

        if ($actor && $previousStatus === 'available' && $status === 'occupied') {
            $desk->ensureFromOccupiedBed($bed->fresh() ?? $bed, $actor, 'direct', $notes);
        }

        if ($actor && $previousStatus === 'occupied' && $status === 'dirty' && $dischargedPatientId) {
            $desk->finalizeReleasedAdmission((int) $dischargedPatientId, $actor);
        }

        return back()->with(
            'status',
            'Bed '.$bed->bed_code.': '.$lifecycle->actionLabel($previousStatus, $status).' completed.'
        );
    }

    public function reserve(ReserveBedRequest $request, Bed $bed, BedOperations $operations): RedirectResponse
    {
        $result = $this->runBedMutation(
            $bed,
            $request->integer('lock_version'),
            function (Bed $freshBed, int $lockVersion) use ($operations, $request): void {
                $operations->reserve(
                    bed: $freshBed,
                    patientId: $request->integer('patient_id'),
                    actor: $request->user(),
                    note: $request->string('reservation_note')->toString() ?: null,
                    until: $request->date('reserved_until'),
                    lockVersion: $lockVersion,
                );
            },
        );

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        return back()->with('status', 'Bed '.$bed->bed_code.' reserved.');
    }

    public function cancelReservation(Request $request, Bed $bed, BedOperations $operations): RedirectResponse
    {
        abort_unless($request->user()?->can('beds.operate'), 403);

        $validated = $request->validate([
            'lock_version' => ['required', 'integer', 'min:0'],
        ]);

        $result = $this->runBedMutation(
            $bed,
            (int) $validated['lock_version'],
            function (Bed $freshBed, int $lockVersion) use ($operations, $request): void {
                $operations->cancelReservation(
                    $freshBed,
                    $request->user(),
                    lockVersion: $lockVersion,
                );
            },
        );

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        return back()->with('status', 'Reservation on '.$bed->bed_code.' cancelled.');
    }

    public function transfer(TransferBedRequest $request, Bed $bed, BedOperations $operations, AdmissionDesk $desk): RedirectResponse
    {
        $toBed = Bed::query()->findOrFail($request->integer('to_bed_id'));
        $recordedTransfer = null;

        $result = $this->runBedMutation(
            $bed,
            $request->integer('lock_version'),
            function (Bed $freshBed, int $lockVersion) use ($operations, $request, $toBed, &$recordedTransfer): void {
                $freshTo = $toBed->fresh() ?? $toBed;

                $recordedTransfer = $operations->transfer(
                    fromBed: $freshBed,
                    toBed: $freshTo,
                    actor: $request->user(),
                    reason: $request->string('reason')->toString() ?: null,
                    fromLockVersion: $lockVersion,
                    toLockVersion: (int) $freshTo->lock_version,
                );
            },
        );

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        if ($recordedTransfer) {
            $desk->syncTransfer($recordedTransfer);
        }

        return back()->with('status', 'Patient transferred from '.$bed->bed_code.' to '.$toBed->bed_code.'.');
    }

    public function inspect(InspectBedRequest $request, Bed $bed, BedLifecycle $lifecycle): RedirectResponse
    {
        $notes = $request->string('notes')->toString() ?: null;
        $passed = $request->string('result')->toString() === 'pass';

        $result = $this->runBedMutation(
            $bed,
            $request->integer('lock_version'),
            function (Bed $freshBed, int $lockVersion) use ($lifecycle, $request, $notes, $passed): void {
                if ($passed) {
                    $lifecycle->passInspection($freshBed, $request->user(), $notes, $lockVersion);
                } else {
                    $lifecycle->failInspection($freshBed, $request->user(), $notes, $lockVersion);
                }
            },
        );

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        return back()->with(
            'status',
            $passed
                ? 'Inspection passed — '.$bed->bed_code.' is available.'
                : 'Inspection failed — '.$bed->bed_code.' returned to dirty.'
        );
    }

    public function maintain(MaintainBedRequest $request, Bed $bed, BedOperations $operations): RedirectResponse
    {
        $starting = $request->string('action')->toString() === 'start';

        $result = $this->runBedMutation(
            $bed,
            $request->integer('lock_version'),
            function (Bed $freshBed, int $lockVersion) use ($operations, $request, $starting): void {
                if ($starting) {
                    $operations->startMaintenance(
                        $freshBed,
                        $request->string('reason')->toString(),
                        $request->user(),
                        $lockVersion,
                    );
                } else {
                    $operations->endMaintenance(
                        $freshBed,
                        $request->user(),
                        $request->string('notes')->toString() ?: null,
                        $lockVersion,
                    );
                }
            },
        );

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        return back()->with(
            'status',
            $starting
                ? 'Maintenance started on '.$bed->bed_code.'.'
                : 'Maintenance ended on '.$bed->bed_code.'.'
        );
    }

    /**
     * Retry once with the freshest lock version so expired reservations / board lag
     * do not block a still-valid floor action.
     *
     * @param  callable(Bed, int): void  $mutation
     */
    private function runBedMutation(Bed $bed, int $lockVersion, callable $mutation): ?RedirectResponse
    {
        try {
            $mutation($bed, $lockVersion);

            return null;
        } catch (ConcurrentBedUpdateException) {
            $bed->refresh();

            try {
                $mutation($bed, (int) $bed->lock_version);

                return null;
            } catch (Throwable $exception) {
                return $this->bedMutationFailed($bed, $exception);
            }
        } catch (InvalidArgumentException $exception) {
            return $this->bedMutationFailed($bed, $exception);
        }
    }

    private function bedMutationFailed(Bed $bed, Throwable $exception): RedirectResponse
    {
        $message = $exception instanceof ConcurrentBedUpdateException
            ? 'Bed '.$bed->bed_code.' changed while you were working. The board was refreshed — try the action again.'
            : $exception->getMessage();

        return redirect()
            ->route('admin.beds.index', array_filter([
                'q' => $bed->bed_code,
                'status' => $bed->fresh()?->status,
            ]))
            ->with('error', $message);
    }
}

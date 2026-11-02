<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\ContactMessage;
use App\Models\LabOrder;
use App\Models\NursingTask;
use App\Models\User;
use App\Models\Visit;
use App\Models\WorkItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WorkInbox
{
    public const RECENT_NOTIFICATIONS = 5;

    /**
     * @return list<string>
     */
    public function desksFor(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        $desks = [];

        if ($user->hasRole('receptionist')) {
            $desks[] = 'reception';
        }

        if ($user->hasRole('nurse_station')) {
            $desks[] = 'nursing';
            $desks[] = 'beds';
        }

        if ($user->hasRole('doctor')) {
            $desks[] = 'doctor';
        }

        if ($user->hasRole('lab_technician')) {
            $desks[] = 'lab';
        }

        if ($user->hasRole('content_manager')) {
            $desks[] = 'content';
        }

        if ($user->hasRole('hospital_admin') || $user->hasRole('super_admin')) {
            $desks[] = 'admin';
        }

        if ($desks === [] && $user->can('appointments.view')) {
            $desks[] = 'reception';
        }

        if ($desks === [] && $user->can('nursing.view')) {
            $desks[] = 'nursing';
        }

        if ($desks === [] && ($user->can('beds.maintain') || $user->can('beds.operate'))) {
            $desks[] = 'beds';
        }

        if ($desks === [] && $user->can('content.manage')) {
            $desks[] = 'content';
        }

        return array_values(array_unique($desks));
    }

    public function primaryDesk(?User $user): string
    {
        return $this->desksFor($user)[0] ?? 'admin';
    }

    public function deskHeading(?User $user): string
    {
        return match ($this->primaryDesk($user)) {
            'reception' => 'Reception Desk',
            'nursing' => 'Nursing Desk',
            'doctor' => 'Doctor Desk',
            'beds' => 'Bed Management Desk',
            'lab' => 'Laboratory Desk',
            'content' => 'Website Desk',
            default => 'Administration Desk',
        };
    }

    public function isVisibleTo(User $user, WorkItem $item): bool
    {
        return $this->visibleQuery($user)->whereKey($item->id)->exists();
    }

    public function unreadNotificationCount(User $user): int
    {
        return $this->visibleQuery($user)
            ->open()
            ->where('is_notification', true)
            ->unread()
            ->count();
    }

    public function notifyAppointmentRequest(Appointment $appointment): void
    {
        $appointment->loadMissing(['patient', 'doctor']);

        $this->upsert($this->websiteRequestItem('reception', $appointment));
        $this->upsert($this->websiteRequestItem('admin', $appointment));

        if ($appointment->doctor_id) {
            $this->upsert($this->websiteRequestItem('doctor', $appointment, (int) $appointment->doctor_id));
        }
    }

    /**
     * @return list<array{desk: string, desk_label: string, kind: string, label: string, tone: string, items: Collection<int, WorkItem>}>
     */
    public function sectionsFor(User $user): array
    {
        $grouped = $this->openFor($user)->groupBy(
            fn (WorkItem $item): string => $item->desk.':'.$item->kind,
        );
        $sections = [];

        foreach ($this->desksFor($user) as $desk) {
            foreach (WorkItem::QUEUE_ORDER[$desk] ?? [] as $kind) {
                /** @var Collection<int, WorkItem> $items */
                $items = $grouped->get($desk.':'.$kind, Collection::make())->values();

                $sections[] = [
                    'desk' => $desk,
                    'desk_label' => WorkItem::DESKS[$desk] ?? ucfirst($desk),
                    'kind' => $kind,
                    'label' => WorkItem::KINDS[$kind] ?? ucfirst(str_replace('_', ' ', $kind)),
                    'tone' => $items->first()?->tone ?: 'orange',
                    'items' => $items,
                ];
            }
        }

        return $sections;
    }

    public function syncFor(?User $user): void
    {
        foreach ($this->desksFor($user) as $desk) {
            $this->syncDesk($desk);
        }
    }

    public function syncDesk(string $desk): void
    {
        $desired = match ($desk) {
            'reception' => $this->receptionItems(),
            'nursing' => $this->nursingItems(),
            'doctor' => $this->doctorItems(),
            'beds' => $this->bedItems(),
            'admin' => $this->adminItems(),
            'lab' => $this->labItems(),
            'content' => $this->contentItems(),
            default => [],
        };

        $this->persist($desk, $desired);
    }

    public function syncAll(): void
    {
        foreach (array_keys(WorkItem::DESKS) as $desk) {
            $this->syncDesk($desk);
        }
    }

    /**
     * @return Collection<int, WorkItem>
     */
    public function openFor(User $user): Collection
    {
        return $this->visibleQuery($user)->open()
            ->orderByRaw('case when is_critical = 1 then 0 else 1 end')
            ->orderBy('due_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, WorkItem>
     */
    public function historyFor(User $user, int $limit = 20): Collection
    {
        return $this->visibleQuery($user)->done()
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<string, Collection<int, WorkItem>>
     */
    public function queuesFor(User $user): array
    {
        $groups = [];

        foreach ($this->openFor($user)->groupBy('kind') as $kind => $items) {
            $groups[(string) $kind] = $items->values();
        }

        return $groups;
    }

    /**
     * @return Collection<int, array{id: string, tone: string, title: string, detail: string, href: string, time: string, read: bool, work_id: int, can_acknowledge: bool, can_escalate: bool}>
     */
    public function notificationsFor(User $user, int $limit = self::RECENT_NOTIFICATIONS): Collection
    {
        return $this->visibleQuery($user)
            ->where('is_notification', true)
            ->orderByRaw('case when read_at is null then 0 else 1 end')
            ->orderByRaw('case when is_critical = 1 then 0 else 1 end')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (WorkItem $item): array => $this->toAlert($item));
    }

    /**
     * @return array{beds: int, pending: int, unpaid: int, messages: int, labs: int}
     */
    public function notificationCounts(User $user): array
    {
        $open = $this->visibleQuery($user)->open()->where('is_notification', true)->unread();

        return [
            'beds' => (clone $open)->whereIn('kind', ['cleaning', 'exception', 'maintenance'])->count(),
            'pending' => (clone $open)->whereIn('kind', ['pending_web', 'arrival'])->count(),
            'unpaid' => (clone $open)->where('kind', 'unpaid')->count(),
            'messages' => (clone $open)->where('kind', 'message')->count(),
            'labs' => (clone $open)->where(fn ($query) => $query->where('kind', 'critical')->orWhere('source_type', 'lab_order'))->count(),
        ];
    }

    /**
     * @param  list<string>  $ids
     */
    public function markRead(User $user, array $ids, bool $all = false): void
    {
        $query = $this->visibleQuery($user)->open()->where('is_notification', true)->unread();

        if (! $all) {
            $workIds = [];

            foreach ($ids as $id) {
                if (str_starts_with($id, 'work:')) {
                    $workIds[] = (int) substr($id, 5);
                }
            }

            if ($workIds === []) {
                return;
            }

            $query->whereIn('id', $workIds);
        }

        $query->update([
            'read_at' => now(),
            'read_by' => $user->id,
        ]);
    }

    public function acknowledge(WorkItem $item, User $user): WorkItem
    {
        if (! $item->isOpen()) {
            return $item;
        }

        $item->forceFill([
            'acknowledged_at' => now(),
            'acknowledged_by' => $user->id,
            'read_at' => $item->read_at ?? now(),
            'read_by' => $item->read_by ?? $user->id,
        ]);

        if (in_array($item->kind, WorkItem::ACK_COMPLETES, true)) {
            $item->forceFill([
                'status' => 'done',
                'completed_at' => now(),
            ]);
        }

        $item->save();

        return $item->refresh();
    }

    public function escalate(WorkItem $item, User $user): WorkItem
    {
        if (! $item->canEscalate()) {
            return $item;
        }

        $item->forceFill([
            'escalated_at' => now(),
            'escalated_by' => $user->id,
            'read_at' => $item->read_at ?? now(),
            'read_by' => $item->read_by ?? $user->id,
        ])->save();

        $this->upsert([
            'work_key' => 'admin:escalated:'.$item->source_type.':'.$item->source_id,
            'desk' => 'admin',
            'kind' => 'escalated',
            'title' => 'Escalated · '.$item->title,
            'detail' => $item->detail,
            'href' => $item->href,
            'tone' => 'red',
            'source_type' => $item->source_type,
            'source_id' => $item->source_id,
            'patient_id' => $item->patient_id,
            'is_critical' => true,
            'is_notification' => true,
            'due_at' => now(),
            'meta' => ['from_work_id' => $item->id],
        ]);

        return $item->refresh();
    }

    public function escalateOverdue(int $minutes = 30): int
    {
        $items = WorkItem::query()
            ->open()
            ->where('is_critical', true)
            ->whereNull('escalated_at')
            ->where('created_at', '<=', now()->subMinutes($minutes))
            ->get();

        foreach ($items as $item) {
            $actor = $item->audienceUser ?? User::query()->orderBy('id')->first();

            if ($actor) {
                $this->escalate($item, $actor);
            }
        }

        return $items->count();
    }

    /**
     * @return array{id: string, tone: string, title: string, detail: string, href: string, time: string, read: bool, work_id: int, can_acknowledge: bool, can_escalate: bool}
     */
    public function toAlert(WorkItem $item): array
    {
        return [
            'id' => 'work:'.$item->id,
            'tone' => $item->tone,
            'title' => $item->title,
            'detail' => (string) $item->detail,
            'href' => $item->href,
            'time' => $item->due_at?->diffForHumans()
                ?: ($item->updated_at?->diffForHumans() ?: ''),
            'read' => $item->read_at !== null,
            'work_id' => $item->id,
            'can_acknowledge' => $item->canAcknowledge(),
            'can_escalate' => $item->canEscalate(),
        ];
    }

    /**
     * @return Builder<WorkItem>
     */
    private function visibleQuery(User $user): Builder
    {
        $desks = $this->desksFor($user);
        $doctorId = $user->doctor?->id;

        return WorkItem::query()
            ->forDesks($desks)
            ->where(function ($query) use ($user, $doctorId): void {
                $query->where(function ($inner) use ($user, $doctorId): void {
                    if ($doctorId !== null) {
                        $inner->where('desk', '!=', 'doctor')
                            ->orWhereNull('audience_doctor_id')
                            ->orWhere('audience_doctor_id', $doctorId);

                        return;
                    }

                    if (! $user->hasRole('doctor')) {
                        $inner->where('desk', '!=', 'doctor')
                            ->orWhereNull('audience_doctor_id');
                    }
                })->where(function ($inner) use ($user): void {
                    $inner->whereNull('audience_user_id')
                        ->orWhere('audience_user_id', $user->id);
                });
            });
    }

    /**
     * @param  list<array<string, mixed>>  $desired
     */
    private function persist(string $desk, array $desired): void
    {
        $keys = [];

        DB::transaction(function () use ($desk, $desired, &$keys): void {
            foreach ($desired as $attributes) {
                $keys[] = $attributes['work_key'];
                $this->upsert($attributes);
            }

            $stale = WorkItem::query()
                ->where('desk', $desk)
                ->open()
                ->when($keys !== [], fn ($query) => $query->whereNotIn('work_key', $keys), fn ($query) => $query)
                ->get();

            foreach ($stale as $item) {
                if (in_array($item->kind, WorkItem::ACK_COMPLETES, true) && $item->isAcknowledged()) {
                    continue;
                }

                $item->forceFill([
                    'status' => 'done',
                    'completed_at' => now(),
                ])->save();
            }
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function upsert(array $attributes): WorkItem
    {
        $existing = WorkItem::query()->where('work_key', $attributes['work_key'])->first();

        if ($existing && $existing->status === 'done' && in_array($existing->kind, WorkItem::ACK_COMPLETES, true)) {
            return $existing;
        }

        $values = [
            ...$attributes,
            'status' => 'open',
            'completed_at' => null,
        ];

        if ($existing) {
            $existing->fill($values)->save();

            return $existing;
        }

        return WorkItem::query()->create($values);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function receptionItems(): array
    {
        $items = $this->websiteRequestItems('reception');

        $arrivals = Appointment::query()
            ->with(['patient', 'visits'])
            ->whereDate('appointment_date', now()->toDateString())
            ->where('status', 'confirmed')
            ->whereDoesntHave('visits')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        foreach ($arrivals as $appointment) {
            $items[] = $this->item(
                desk: 'reception',
                kind: 'arrival',
                source: $appointment,
                title: 'Arrival '.$appointment->appointment_no,
                detail: ($appointment->patient?->full_name ?: 'Patient').' · '.substr((string) $appointment->start_time, 0, 5),
                href: route('admin.appointments.show', $appointment),
                tone: 'blue',
            );
        }

        foreach (Visit::query()->with('patient')->where('status', 'waiting')->orderBy('arrived_at')->orderBy('id')->get() as $visit) {
            $items[] = $this->item(
                desk: 'reception',
                kind: 'waiting',
                source: $visit,
                title: 'Waiting '.$visit->visit_no,
                detail: ($visit->patient?->full_name ?: 'Patient').' is in the waiting area.',
                href: route('admin.visits.show', $visit),
                tone: 'orange',
            );
        }

        foreach (Appointment::query()->with('patient')->where('status', 'no_show')->whereDate('appointment_date', now()->toDateString())->orderBy('id')->get() as $appointment) {
            $items[] = $this->item(
                desk: 'reception',
                kind: 'no_show',
                source: $appointment,
                title: 'No-show '.$appointment->appointment_no,
                detail: ($appointment->patient?->full_name ?: 'Patient').' did not arrive.',
                href: route('admin.appointments.show', $appointment),
                tone: 'red',
                notify: true,
            );
        }

        return $items;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function nursingItems(): array
    {
        $items = [];

        foreach (Bed::query()->with(['patient', 'ward'])->occupied()->orderBy('bed_code')->orderBy('id')->get() as $bed) {
            $items[] = $this->item(
                desk: 'nursing',
                kind: 'assigned',
                source: $bed,
                title: ($bed->patient?->full_name ?: 'Patient').' · '.$bed->bed_code,
                detail: ($bed->ward?->name ?: 'Ward').' · assigned bed',
                href: $bed->patient
                    ? route('admin.nursing.patients.show', $bed->patient)
                    : route('admin.beds.index', ['q' => $bed->bed_code]),
                tone: 'blue',
                patientId: $bed->patient_id,
            );
        }

        foreach (NursingTask::query()->with('patient')->overdue()->orderBy('due_at')->orderBy('id')->get() as $task) {
            $kind = $task->task_type === 'observation' ? 'overdue_observation' : 'pending_task';
            $items[] = $this->item(
                desk: 'nursing',
                kind: $kind,
                source: $task,
                title: $task->title,
                detail: ($task->patient?->full_name ?: 'Patient').' · due '.$task->due_at?->format('H:i'),
                href: $task->patient
                    ? route('admin.nursing.patients.show', $task->patient)
                    : route('admin.nursing.index'),
                tone: 'red',
                notify: true,
                critical: $kind === 'overdue_observation',
                patientId: $task->patient_id,
                dueAt: $task->due_at,
            );
        }

        foreach (NursingTask::query()->with('patient')->open()->where(function ($query): void {
            $query->whereNull('due_at')->orWhere('due_at', '>=', now());
        })->orderBy('due_at')->orderBy('id')->get() as $task) {
            $items[] = $this->item(
                desk: 'nursing',
                kind: 'pending_task',
                source: $task,
                title: $task->title,
                detail: ($task->patient?->full_name ?: 'Patient').' · '.$task->statusLabel(),
                href: $task->patient
                    ? route('admin.nursing.patients.show', $task->patient)
                    : route('admin.nursing.index'),
                tone: 'orange',
                patientId: $task->patient_id,
                dueAt: $task->due_at,
            );
        }

        foreach (LabOrder::query()->critical()->with(['patient', 'test', 'result', 'visit'])->orderByDesc('id')->get() as $order) {
            $items[] = $this->item(
                desk: 'nursing',
                kind: 'critical',
                source: $order,
                title: 'Critical lab '.$order->order_no,
                detail: ($order->patient?->full_name ?: 'Patient').' · '.($order->test?->name ?: 'Test').' '.$order->result?->displayValue(),
                href: $order->visit ? route('admin.visits.show', $order->visit) : route('admin.labs.show', $order),
                tone: 'red',
                notify: true,
                critical: true,
                patientId: $order->patient_id,
            );
        }

        return $items;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function doctorItems(): array
    {
        $items = $this->websiteRequestItems('doctor');

        $consults = Visit::query()
            ->with(['patient', 'doctor'])
            ->whereIn('status', ['waiting', 'triage'])
            ->orderBy('arrived_at')
            ->orderBy('id')
            ->get();

        foreach ($consults as $visit) {
            $items[] = $this->item(
                desk: 'doctor',
                kind: 'consult',
                source: $visit,
                title: 'Consultation '.$visit->visit_no,
                detail: ($visit->patient?->full_name ?: 'Patient').' · '.$visit->statusLabel(),
                href: route('admin.visits.show', $visit),
                tone: 'orange',
                patientId: $visit->patient_id,
                doctorId: $visit->doctor_id,
            );
        }

        $assigned = Visit::query()
            ->with('patient')
            ->open()
            ->whereNotNull('doctor_id')
            ->orderByDesc('arrived_at')
            ->orderByDesc('id')
            ->get();

        foreach ($assigned as $visit) {
            $items[] = $this->item(
                desk: 'doctor',
                kind: 'assigned',
                source: $visit,
                title: ($visit->patient?->full_name ?: 'Patient').' · '.$visit->visit_no,
                detail: 'Assigned encounter · '.$visit->statusLabel(),
                href: route('admin.visits.show', $visit),
                tone: 'blue',
                patientId: $visit->patient_id,
                doctorId: $visit->doctor_id,
            );
        }

        foreach (LabOrder::query()->open()->with(['patient', 'test', 'visit'])->orderBy('id')->get() as $order) {
            $items[] = $this->item(
                desk: 'doctor',
                kind: 'result',
                source: $order,
                title: 'Outstanding '.$order->order_no,
                detail: ($order->patient?->full_name ?: 'Patient').' · '.($order->test?->name ?: 'Test').' · '.$order->statusLabel(),
                href: route('admin.labs.show', $order),
                tone: 'orange',
                patientId: $order->patient_id,
                doctorId: $order->visit?->doctor_id,
            );
        }

        foreach (LabOrder::query()->critical()->with(['patient', 'test', 'result', 'visit'])->orderByDesc('id')->get() as $order) {
            $items[] = $this->item(
                desk: 'doctor',
                kind: 'critical',
                source: $order,
                title: 'Critical lab '.$order->order_no,
                detail: ($order->patient?->full_name ?: 'Patient').' · '.($order->test?->name ?: 'Test').' '.$order->result?->displayValue(),
                href: $order->visit ? route('admin.visits.show', $order->visit) : route('admin.labs.show', $order),
                tone: 'red',
                notify: true,
                critical: true,
                patientId: $order->patient_id,
                doctorId: $order->visit?->doctor_id,
            );
        }

        $followUps = Visit::query()
            ->with('patient')
            ->where('status', 'completed')
            ->whereNotNull('follow_up_instructions')
            ->where('completed_at', '>=', now()->subDays(14))
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get();

        foreach ($followUps as $visit) {
            $items[] = $this->item(
                desk: 'doctor',
                kind: 'follow_up',
                source: $visit,
                title: 'Follow-up '.$visit->visit_no,
                detail: ($visit->patient?->full_name ?: 'Patient').' · '.$visit->follow_up_instructions,
                href: route('admin.visits.show', $visit),
                tone: 'blue',
                notify: true,
                patientId: $visit->patient_id,
                doctorId: $visit->doctor_id,
            );
        }

        return $items;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function bedItems(): array
    {
        $items = [];

        foreach (Bed::query()->with('ward')->available()->where('is_maintenance', false)->orderBy('bed_code')->get() as $bed) {
            if (! $bed->isAdmittable()) {
                continue;
            }

            $items[] = $this->item(
                desk: 'beds',
                kind: 'ready',
                source: $bed,
                title: 'Ready '.$bed->bed_code,
                detail: ($bed->ward?->name ?: 'Ward').' can admit now.',
                href: route('admin.beds.index', ['q' => $bed->bed_code, 'status' => 'available']),
                tone: 'blue',
            );
        }

        $emergencies = Appointment::query()
            ->with('patient')
            ->emergencyDesk(now()->toDateString())
            ->where('status', 'confirmed')
            ->whereDoesntHave('patient.admissions', fn ($query) => $query->open())
            ->orderBy('id')
            ->get();

        foreach ($emergencies as $appointment) {
            $items[] = $this->item(
                desk: 'beds',
                kind: 'pending_admission',
                source: $appointment,
                title: 'Pending admission '.$appointment->appointment_no,
                detail: ($appointment->patient?->full_name ?: 'Patient').' needs a bed.',
                href: route('admin.admissions.create', ['source' => 'emergency', 'appointment_id' => $appointment->id]),
                tone: 'orange',
                notify: true,
                patientId: $appointment->patient_id,
            );
        }

        foreach (Bed::query()->with('ward')->whereIn('status', ['dirty', 'cleaning'])->orderBy('bed_code')->get() as $bed) {
            $items[] = $this->item(
                desk: 'beds',
                kind: 'cleaning',
                source: $bed,
                title: $bed->status === 'dirty'
                    ? 'Bed '.$bed->bed_code.' needs cleaning'
                    : 'Bed '.$bed->bed_code.' is being cleaned',
                detail: ($bed->ward?->name ?: 'Ward').' needs turnaround.',
                href: route('admin.beds.index', ['q' => $bed->bed_code, 'status' => $bed->status]),
                tone: $bed->status === 'dirty' ? 'red' : 'orange',
                notify: true,
                critical: $bed->status === 'dirty',
            );
        }

        foreach (Bed::query()->with('ward')->where('is_maintenance', true)->orderBy('bed_code')->get() as $bed) {
            $items[] = $this->item(
                desk: 'beds',
                kind: 'maintenance',
                source: $bed,
                title: 'Maintenance '.$bed->bed_code,
                detail: $bed->maintenance_reason ?: (($bed->ward?->name ?: 'Ward').' is blocked for maintenance.'),
                href: route('admin.beds.index', ['q' => $bed->bed_code]),
                tone: 'orange',
            );
        }

        return $items;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function adminItems(): array
    {
        $items = $this->websiteRequestItems('admin');

        foreach (Bill::query()->unpaid()->with('patient')->orderByDesc('id')->get() as $bill) {
            $items[] = $this->item(
                desk: 'admin',
                kind: 'unpaid',
                source: $bill,
                title: 'Unpaid bill '.$bill->bill_no,
                detail: ($bill->patient?->full_name ?: 'Patient').' · '.number_format((float) $bill->total, 2),
                href: route('admin.bills.show', $bill),
                tone: 'red',
                notify: true,
                patientId: $bill->patient_id,
            );
        }

        foreach (Bed::query()->with('ward')->whereIn('status', ['dirty', 'cleaning'])->orderBy('bed_code')->get() as $bed) {
            $items[] = $this->item(
                desk: 'admin',
                kind: 'exception',
                source: $bed,
                title: $bed->status === 'dirty'
                    ? 'Bed '.$bed->bed_code.' needs cleaning'
                    : 'Bed '.$bed->bed_code.' is being cleaned',
                detail: ($bed->ward?->name ?: 'Ward').' turnaround is still open.',
                href: route('admin.beds.index', ['q' => $bed->bed_code, 'status' => $bed->status]),
                tone: $bed->status === 'dirty' ? 'red' : 'orange',
                notify: true,
                critical: $bed->status === 'dirty',
            );
        }

        foreach (LabOrder::query()->critical()->with(['patient', 'test', 'result', 'visit'])->orderByDesc('id')->get() as $order) {
            $items[] = $this->item(
                desk: 'admin',
                kind: 'critical',
                source: $order,
                title: 'Critical lab '.$order->order_no,
                detail: ($order->patient?->full_name ?: 'Patient').' · '.($order->test?->name ?: 'Test').' '.$order->result?->displayValue(),
                href: $order->visit ? route('admin.visits.show', $order->visit) : route('admin.labs.show', $order),
                tone: 'red',
                notify: true,
                critical: true,
                patientId: $order->patient_id,
            );
        }

        foreach (WorkItem::query()->open()->whereNotNull('escalated_at')->where('desk', '!=', 'admin')->orderByDesc('escalated_at')->get() as $escalated) {
            $items[] = $this->item(
                desk: 'admin',
                kind: 'escalated',
                sourceType: $escalated->source_type,
                sourceId: $escalated->source_id,
                title: 'Escalated · '.$escalated->title,
                detail: $escalated->detail,
                href: $escalated->href,
                tone: 'red',
                notify: true,
                critical: true,
                patientId: $escalated->patient_id,
            );
        }

        return $items;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function labItems(): array
    {
        $items = [];

        foreach (LabOrder::query()->open()->with(['patient', 'test'])->orderByRaw("case status when 'processing' then 1 when 'collected' then 2 else 3 end")->orderBy('id')->get() as $order) {
            $items[] = $this->item(
                desk: 'lab',
                kind: 'lab_work',
                source: $order,
                title: $order->order_no.' · '.($order->test?->name ?: 'Test'),
                detail: ($order->patient?->full_name ?: 'Patient').' · '.$order->statusLabel(),
                href: route('admin.labs.show', $order),
                tone: $order->status === 'processing' ? 'orange' : 'blue',
                patientId: $order->patient_id,
            );
        }

        return $items;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function contentItems(): array
    {
        $items = [];

        foreach (ContactMessage::query()->unread()->orderByDesc('id')->get() as $message) {
            $items[] = $this->item(
                desk: 'content',
                kind: 'message',
                source: $message,
                title: 'Unread message from '.$message->name,
                detail: $message->subject ?: 'Website contact form',
                href: route('admin.contact-messages.show', $message),
                tone: 'blue',
                notify: true,
            );
        }

        return $items;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function websiteRequestItems(string $desk): array
    {
        $items = [];

        foreach ($this->pendingWebsiteAppointments() as $appointment) {
            $items[] = $this->websiteRequestItem(
                $desk,
                $appointment,
                $desk === 'doctor' ? (int) $appointment->doctor_id : null,
            );
        }

        return $items;
    }

    /**
     * @return Collection<int, Appointment>
     */
    private function pendingWebsiteAppointments(): Collection
    {
        return Appointment::query()
            ->with(['patient', 'doctor'])
            ->where('source', 'web')
            ->where('status', 'pending')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function websiteRequestItem(string $desk, Appointment $appointment, ?int $doctorId = null): array
    {
        $date = $appointment->appointment_date?->format('d M Y') ?: 'a clinic day';
        $time = substr((string) $appointment->start_time, 0, 5);
        $when = $time !== '' ? $date.' at '.$time : $date;

        return $this->item(
            desk: $desk,
            kind: 'pending_web',
            source: $appointment,
            title: 'New appointment '.$appointment->appointment_no,
            detail: ($appointment->patient?->full_name ?: 'Patient')
                .' booked '.($appointment->doctor?->name ?: 'a doctor')
                .' on '.$when,
            href: route('admin.appointments.show', $appointment),
            tone: 'orange',
            notify: true,
            patientId: $appointment->patient_id,
            doctorId: $doctorId,
        );
    }

    /**
     * @param  Appointment|Visit|Bed|NursingTask|LabOrder|Bill|ContactMessage|null  $source
     * @return array<string, mixed>
     */
    private function item(
        string $desk,
        string $kind,
        ?object $source = null,
        string $title = '',
        ?string $detail = null,
        string $href = '/',
        string $tone = 'orange',
        bool $notify = false,
        bool $critical = false,
        ?int $patientId = null,
        ?int $doctorId = null,
        mixed $dueAt = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
    ): array {
        $type = $sourceType ?? match (true) {
            $source instanceof Appointment => 'appointment',
            $source instanceof Visit => 'visit',
            $source instanceof Bed => 'bed',
            $source instanceof NursingTask => 'nursing_task',
            $source instanceof LabOrder => 'lab_order',
            $source instanceof Bill => 'bill',
            $source instanceof ContactMessage => 'contact_message',
            default => 'work',
        };
        $id = $sourceId ?? (int) ($source?->id ?? 0);

        return [
            'work_key' => $desk.':'.$kind.':'.$type.':'.$id,
            'desk' => $desk,
            'kind' => $kind,
            'title' => $title,
            'detail' => $detail,
            'href' => $href,
            'tone' => $tone,
            'source_type' => $type,
            'source_id' => $id,
            'patient_id' => $patientId,
            'audience_doctor_id' => $doctorId,
            'is_critical' => $critical,
            'is_notification' => $notify || $critical,
            'due_at' => $dueAt,
        ];
    }
}

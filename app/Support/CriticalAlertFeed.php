<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Support\Collection;

class CriticalAlertFeed
{
    /** @var array<string, array{alerts: Collection<int, array{tone: string, title: string, detail: string, href: string, time: string}>, counts: array{beds: int, pending: int, unpaid: int, messages: int}, total: int}> */
    private array $memo = [];

    /**
     * @return array{alerts: Collection<int, array{tone: string, title: string, detail: string, href: string, time: string}>, counts: array{beds: int, pending: int, unpaid: int, messages: int}, total: int}
     */
    public function for(?User $user, int $limit = 30): array
    {
        $key = 'for:'.($user?->getAuthIdentifier() ?? 'guest').':'.$limit;

        return $this->memo[$key] ??= $this->build(
            includeBeds: $user?->can('operations.manage') ?? false,
            includePending: $user?->can('appointments.view') ?? false,
            includeUnpaid: $user?->can('billing.manage') ?? false,
            includeMessages: $user?->can('content.manage') ?? false,
            limit: $limit,
        );
    }

    /**
     * Full operations desk feed used by the Critical Alerts page.
     *
     * @return array{alerts: Collection<int, array{tone: string, title: string, detail: string, href: string, time: string}>, counts: array{beds: int, pending: int, unpaid: int, messages: int}, total: int}
     */
    public function all(int $limit = 30): array
    {
        return $this->build(
            includeBeds: true,
            includePending: true,
            includeUnpaid: true,
            includeMessages: true,
            limit: $limit,
        );
    }

    /**
     * @return array{alerts: Collection<int, array{tone: string, title: string, detail: string, href: string, time: string}>, counts: array{beds: int, pending: int, unpaid: int, messages: int}, total: int}
     */
    private function build(
        bool $includeBeds,
        bool $includePending,
        bool $includeUnpaid,
        bool $includeMessages,
        int $limit,
    ): array {
        $alerts = Collection::make();
        $counts = [
            'beds' => 0,
            'pending' => 0,
            'unpaid' => 0,
            'messages' => 0,
        ];

        if ($includeBeds) {
            $counts['beds'] = Bed::query()->whereIn('status', ['dirty', 'cleaning'])->count();

            Bed::query()->with('ward')->whereIn('status', ['dirty', 'cleaning'])->orderBy('bed_code')->limit(20)->get()
                ->each(function (Bed $bed) use ($alerts): void {
                    $alerts->push([
                        'tone' => $bed->status === 'dirty' ? 'red' : 'orange',
                        'title' => 'Bed '.$bed->bed_code.' is '.$bed->status,
                        'detail' => ($bed->ward?->name ?: 'Ward').' needs turnaround.',
                        'href' => route('admin.beds.index', ['status' => $bed->status]),
                        'time' => $bed->updated_at?->diffForHumans() ?: '',
                    ]);
                });
        }

        if ($includePending) {
            $counts['pending'] = Appointment::query()
                ->where('status', 'pending')
                ->whereDate('appointment_date', '>=', now()->toDateString())
                ->count();

            Appointment::query()->with('patient')->where('status', 'pending')->whereDate('appointment_date', '>=', now()->toDateString())->orderBy('appointment_date')->limit(10)->get()
                ->each(function (Appointment $appointment) use ($alerts): void {
                    $alerts->push([
                        'tone' => 'orange',
                        'title' => 'Pending visit '.$appointment->appointment_no,
                        'detail' => ($appointment->patient?->full_name ?: 'Patient').' still needs confirmation.',
                        'href' => route('admin.appointments.show', $appointment),
                        'time' => $appointment->created_at?->diffForHumans() ?: '',
                    ]);
                });
        }

        if ($includeUnpaid) {
            $counts['unpaid'] = Bill::query()->unpaid()->count();

            Bill::query()->unpaid()->with('patient')->orderByDesc('id')->limit(8)->get()
                ->each(function (Bill $bill) use ($alerts): void {
                    $alerts->push([
                        'tone' => 'red',
                        'title' => 'Unpaid bill '.$bill->bill_no,
                        'detail' => ($bill->patient?->full_name ?: 'Patient').' · '.number_format((float) $bill->total, 2),
                        'href' => route('admin.bills.show', $bill),
                        'time' => $bill->bill_date?->diffForHumans() ?: '',
                    ]);
                });
        }

        if ($includeMessages) {
            $counts['messages'] = ContactMessage::query()->unread()->count();

            ContactMessage::query()->unread()->orderByDesc('id')->limit(5)->get()
                ->each(function (ContactMessage $message) use ($alerts): void {
                    $alerts->push([
                        'tone' => 'blue',
                        'title' => 'Unread message from '.$message->name,
                        'detail' => $message->subject ?: 'Website contact form',
                        'href' => route('admin.contact-messages.show', $message),
                        'time' => $message->created_at?->diffForHumans() ?: '',
                    ]);
                });
        }

        $trimmed = $alerts->take($limit)->values();

        return [
            'alerts' => $trimmed,
            'counts' => $counts,
            'total' => array_sum($counts),
        ];
    }
}

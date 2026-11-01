<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\BedEvent;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Ward;
use Illuminate\Support\Carbon;

class OperationalReport
{
    /**
     * @return array{
     *     period: string,
     *     from: string,
     *     to: string,
     *     label: string,
     *     kpis: list<array{label: string, value: string, meta: string, tone: string, href: string}>,
     *     beds: array{total: int, occupied: int, ready: int, turnaround: int, maintenance: int, percent: int, admitted: int, wards: list<array{name: string, type: string, total: int, occupied: int, percent: int}>},
     *     visits: array{total: int, pending: int, confirmed: int, completed: int, cancelled: int, emergency: int},
     *     billing: array{billed: string, collected: string, unpaid: int},
     *     people: array{patients: int, doctors: int}
     * }
     */
    public function forPeriod(string $period): array
    {
        [$from, $to, $label] = $this->range($period);
        $fromDay = Carbon::parse($from)->startOfDay();
        $toDay = Carbon::parse($to)->endOfDay();

        $bedRows = Bed::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $occupied = (int) ($bedRows['occupied'] ?? 0);
        $available = (int) ($bedRows['available'] ?? 0);
        $dirty = (int) ($bedRows['dirty'] ?? 0);
        $cleaning = (int) ($bedRows['cleaning'] ?? 0);
        $totalBeds = $occupied + $available + $dirty + $cleaning;
        $ready = Bed::query()->admittable()->count();
        $turnaround = $dirty + $cleaning;
        $maintenance = Bed::query()->where('is_maintenance', true)->count();
        $occupancyPercent = $totalBeds > 0 ? (int) round(($occupied / $totalBeds) * 100) : 0;

        $visitRow = Appointment::query()
            ->toBase()
            ->whereDate('appointment_date', '>=', $from)
            ->whereDate('appointment_date', '<=', $to)
            ->selectRaw("count(case when status <> 'cancelled' then 1 end) as total")
            ->selectRaw("count(case when status = 'pending' then 1 end) as pending")
            ->selectRaw("count(case when status = 'confirmed' then 1 end) as confirmed")
            ->selectRaw("count(case when status = 'completed' then 1 end) as completed")
            ->selectRaw("count(case when status = 'cancelled' then 1 end) as cancelled")
            ->first();

        $visits = (int) ($visitRow->total ?? 0);
        $pending = (int) ($visitRow->pending ?? 0);
        $confirmed = (int) ($visitRow->confirmed ?? 0);
        $completed = (int) ($visitRow->completed ?? 0);
        $cancelled = (int) ($visitRow->cancelled ?? 0);

        $emergency = Appointment::query()
            ->whereDate('appointment_date', '>=', $from)
            ->whereDate('appointment_date', '<=', $to)
            ->emergency()
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $admitted = BedEvent::query()
            ->where('event_type', BedEvent::TYPE_ADMITTED)
            ->whereBetween('created_at', [$fromDay, $toDay])
            ->count();

        $billed = (float) Bill::query()
            ->whereDate('bill_date', '>=', $from)
            ->whereDate('bill_date', '<=', $to)
            ->whereIn('status', ['issued', 'paid'])
            ->sum('total');

        $collected = (float) Bill::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$fromDay, $toDay])
            ->sum('paid_amount');

        $unpaid = Bill::query()->unpaid()->count();

        $wards = Ward::query()
            ->active()
            ->withCount([
                'beds',
                'occupiedBeds',
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->map(fn (Ward $ward): array => [
                'name' => $ward->name,
                'type' => $ward->typeLabel(),
                'total' => (int) $ward->beds_count,
                'occupied' => (int) $ward->occupied_beds_count,
                'percent' => $ward->occupancyPercent(),
            ])
            ->all();

        return [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'label' => $label,
            'kpis' => [
                [
                    'label' => 'Occupancy',
                    'value' => $occupancyPercent.'%',
                    'meta' => $occupied.' of '.$totalBeds.' beds',
                    'tone' => 'blue',
                    'href' => route('admin.beds.index', ['status' => 'occupied']),
                ],
                [
                    'label' => 'Ready to admit',
                    'value' => (string) $ready,
                    'meta' => $turnaround.' in turnaround · '.$maintenance.' maintenance',
                    'tone' => 'green',
                    'href' => route('admin.beds.index', ['status' => 'available']),
                ],
                [
                    'label' => 'Visits in period',
                    'value' => (string) $visits,
                    'meta' => $completed.' completed · '.$emergency.' emergency',
                    'tone' => 'orange',
                    'href' => route('admin.appointments.index'),
                ],
                [
                    'label' => 'Collected',
                    'value' => number_format($collected, 2),
                    'meta' => $unpaid.' unpaid · billed '.number_format($billed, 2),
                    'tone' => 'navy',
                    'href' => route('admin.bills.index'),
                ],
            ],
            'beds' => [
                'total' => $totalBeds,
                'occupied' => $occupied,
                'ready' => $ready,
                'turnaround' => $turnaround,
                'maintenance' => $maintenance,
                'percent' => $occupancyPercent,
                'admitted' => $admitted,
                'wards' => $wards,
            ],
            'visits' => [
                'total' => $visits,
                'pending' => $pending,
                'confirmed' => $confirmed,
                'completed' => $completed,
                'cancelled' => $cancelled,
                'emergency' => $emergency,
            ],
            'billing' => [
                'billed' => number_format($billed, 2),
                'collected' => number_format($collected, 2),
                'unpaid' => $unpaid,
            ],
            'people' => [
                'patients' => Patient::query()->count(),
                'doctors' => Doctor::query()->active()->count(),
            ],
        ];
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function range(string $period): array
    {
        return match ($period) {
            'today' => [
                now()->toDateString(),
                now()->toDateString(),
                'Today · '.now()->format('j M Y'),
            ],
            '7d' => [
                now()->subDays(6)->toDateString(),
                now()->toDateString(),
                'Last 7 days',
            ],
            default => [
                now()->startOfWeek(Carbon::MONDAY)->toDateString(),
                now()->endOfWeek(Carbon::SUNDAY)->toDateString(),
                'This week · '.now()->startOfWeek(Carbon::MONDAY)->format('j M').'–'.now()->endOfWeek(Carbon::SUNDAY)->format('j M Y'),
            ],
        };
    }
}

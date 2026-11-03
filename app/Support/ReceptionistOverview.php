<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Collection;

class ReceptionistOverview
{
    /**
     * @return array{
     *     enabled: bool,
     *     kpis: list<array{label: string, value: string, meta: string, href: ?string, icon: string}>,
     *     doctorSchedules: Collection<int, object{name: string, department: string, slots: string, appointments: int}>,
     *     waitingPatients: Collection<int, Visit>,
     *     pendingSerials: Collection<int, Appointment>,
     *     searchHref: string
     * }
     */
    public function for(?User $user): array
    {
        if ($user === null || ! $user->hasRole('receptionist')) {
            return [
                'enabled' => false,
                'kpis' => [],
                'doctorSchedules' => Collection::make(),
                'waitingPatients' => Collection::make(),
                'pendingSerials' => Collection::make(),
                'searchHref' => route('admin.patients.index'),
            ];
        }

        $todayAppointments = Appointment::query()
            ->whereDate('appointment_date', today())
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $waiting = Visit::query()->where('status', 'waiting')->count();
        $registered = Patient::query()->count();
        $pendingSerials = Appointment::query()
            ->whereDate('appointment_date', today())
            ->where(function ($query): void {
                $query->where('status', 'pending')
                    ->orWhere(function ($inner): void {
                        $inner->whereIn('status', ['confirmed', 'completed'])
                            ->whereNull('serial_no');
                    });
            })
            ->count();

        $doctorsOnDuty = Doctor::query()
            ->active()
            ->whereHas('doctorSchedules', fn ($query) => $query->where('is_active', true)
                ->where('weekday', strtolower(now()->format('l'))))
            ->count();

        return [
            'enabled' => true,
            'kpis' => [
                [
                    'label' => "Today's Appointments",
                    'value' => number_format($todayAppointments),
                    'meta' => today()->format('j M Y'),
                    'href' => route('admin.appointments.index', ['date' => today()->toDateString()]),
                    'icon' => 'clipboard',
                ],
                [
                    'label' => 'Waiting Patients',
                    'value' => number_format($waiting),
                    'meta' => 'Checked in at reception',
                    'href' => route('admin.visits.index'),
                    'icon' => 'users',
                ],
                [
                    'label' => 'Registered Patients',
                    'value' => number_format($registered),
                    'meta' => 'Patient registry',
                    'href' => route('admin.patients.index'),
                    'icon' => 'badge',
                ],
                [
                    'label' => 'Doctor Schedule',
                    'value' => number_format($doctorsOnDuty),
                    'meta' => 'Clinic sessions today',
                    'href' => route('admin.appointments.index'),
                    'icon' => 'stethoscope',
                ],
                [
                    'label' => 'Pending Serial Numbers',
                    'value' => number_format($pendingSerials),
                    'meta' => 'Awaiting confirm / serial',
                    'href' => route('admin.appointments.index', ['status' => 'pending']),
                    'icon' => 'clipboard-plus',
                ],
                [
                    'label' => 'Patient Search',
                    'value' => 'Find',
                    'meta' => 'Name, phone, or Patient ID',
                    'href' => route('admin.patients.index'),
                    'icon' => 'search',
                ],
            ],
            'doctorSchedules' => $this->doctorSchedulesToday(),
            'waitingPatients' => Visit::query()
                ->with(['patient', 'doctor'])
                ->where('status', 'waiting')
                ->orderBy('arrived_at')
                ->limit(8)
                ->get(),
            'pendingSerials' => Appointment::query()
                ->with(['patient', 'doctor'])
                ->whereDate('appointment_date', today())
                ->where(function ($query): void {
                    $query->where('status', 'pending')
                        ->orWhere(function ($inner): void {
                            $inner->whereIn('status', ['confirmed', 'completed'])
                                ->whereNull('serial_no');
                        });
                })
                ->orderBy('start_time')
                ->limit(8)
                ->get(),
            'searchHref' => route('admin.patients.index'),
        ];
    }

    /**
     * @return Collection<int, object{name: string, department: string, slots: string, appointments: int}>
     */
    private function doctorSchedulesToday(): Collection
    {
        $weekday = strtolower(now()->format('l'));
        $today = today()->toDateString();

        return Doctor::query()
            ->active()
            ->with(['department', 'doctorSchedules' => fn ($query) => $query->where('weekday', $weekday)->where('is_active', true)])
            ->withCount([
                'appointments as today_appointments' => fn ($query) => $query
                    ->whereDate('appointment_date', $today)
                    ->whereNotIn('status', ['cancelled']),
            ])
            ->orderBy('name')
            ->get()
            ->filter(fn (Doctor $doctor): bool => $doctor->doctorSchedules->isNotEmpty() || (int) $doctor->today_appointments > 0)
            ->take(8)
            ->map(function (Doctor $doctor): object {
                $slots = $doctor->doctorSchedules
                    ->map(fn (DoctorSchedule $schedule): string => substr((string) $schedule->start_time, 0, 5).'–'.substr((string) $schedule->end_time, 0, 5))
                    ->implode(', ');

                return (object) [
                    'name' => $doctor->name,
                    'department' => $doctor->department?->name ?: '—',
                    'slots' => $slots !== '' ? $slots : 'Visits booked',
                    'appointments' => (int) $doctor->today_appointments,
                ];
            })
            ->values();
    }
}

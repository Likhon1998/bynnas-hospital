<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\LabOrder;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Collection;

class DoctorOverview
{
    /**
     * @return array{
     *     enabled: bool,
     *     doctor: ?Doctor,
     *     kpis: list<array{label: string, value: string, meta: string, href: ?string, icon: string}>,
     *     queue: Collection<int, Visit>,
     *     waiting: Collection<int, Visit>,
     *     completed: Collection<int, Visit>,
     *     followUps: Collection<int, Visit>,
     *     pendingLabs: Collection<int, LabOrder>
     * }
     */
    public function for(?User $user): array
    {
        $doctor = $user?->doctor;

        if ($user === null || ! $user->hasRole('doctor') || $doctor === null) {
            return [
                'enabled' => false,
                'doctor' => null,
                'kpis' => [],
                'queue' => Collection::make(),
                'waiting' => Collection::make(),
                'completed' => Collection::make(),
                'followUps' => Collection::make(),
                'pendingLabs' => Collection::make(),
            ];
        }

        $todayAppointments = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', today())
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $queue = Visit::query()
            ->where('doctor_id', $doctor->id)
            ->whereIn('status', ['waiting', 'triage', 'consultation'])
            ->whereDate('arrived_at', today())
            ->count();

        $waiting = Visit::query()
            ->where('doctor_id', $doctor->id)
            ->where('status', 'waiting')
            ->count();

        $completed = Visit::query()
            ->where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();

        $followUps = Visit::query()
            ->where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->whereNotNull('follow_up_instructions')
            ->where('completed_at', '>=', now()->subDays(14))
            ->count();

        $pendingLabs = LabOrder::query()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereHas('visit', fn ($visit) => $visit->where('doctor_id', $doctor->id))
            ->count();

        return [
            'enabled' => true,
            'doctor' => $doctor,
            'kpis' => [
                [
                    'label' => "Today's Appointments",
                    'value' => number_format($todayAppointments),
                    'meta' => today()->format('j M Y'),
                    'href' => route('admin.appointments.index', ['doctor' => $doctor->id, 'date' => today()->toDateString()]),
                    'icon' => 'clipboard',
                ],
                [
                    'label' => "Today's Patient Queue",
                    'value' => number_format($queue),
                    'meta' => 'Assigned open encounters',
                    'href' => route('admin.visits.index'),
                    'icon' => 'users',
                ],
                [
                    'label' => 'Waiting Patients',
                    'value' => number_format($waiting),
                    'meta' => 'Ready for consultation',
                    'href' => route('admin.visits.index', ['status' => 'waiting']),
                    'icon' => 'clock',
                ],
                [
                    'label' => 'Completed Consultations',
                    'value' => number_format($completed),
                    'meta' => 'Finished today',
                    'href' => route('admin.visits.index', ['status' => 'completed']),
                    'icon' => 'clipboard-check',
                ],
                [
                    'label' => 'Follow-up Patients',
                    'value' => number_format($followUps),
                    'meta' => 'Last 14 days with follow-up notes',
                    'href' => route('admin.visits.index', ['status' => 'completed']),
                    'icon' => 'calendar',
                ],
                [
                    'label' => 'Pending Laboratory Reports',
                    'value' => number_format($pendingLabs),
                    'meta' => 'Open lab / imaging orders',
                    'href' => route('admin.labs.index'),
                    'icon' => 'flask',
                ],
            ],
            'queue' => Visit::query()
                ->with('patient')
                ->where('doctor_id', $doctor->id)
                ->whereIn('status', ['waiting', 'triage', 'consultation'])
                ->whereDate('arrived_at', today())
                ->orderByRaw("case status when 'consultation' then 0 when 'triage' then 1 else 2 end")
                ->orderBy('arrived_at')
                ->limit(8)
                ->get(),
            'waiting' => Visit::query()
                ->with('patient')
                ->where('doctor_id', $doctor->id)
                ->where('status', 'waiting')
                ->orderBy('arrived_at')
                ->limit(8)
                ->get(),
            'completed' => Visit::query()
                ->with('patient')
                ->where('doctor_id', $doctor->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->latest('completed_at')
                ->limit(8)
                ->get(),
            'followUps' => Visit::query()
                ->with('patient')
                ->where('doctor_id', $doctor->id)
                ->where('status', 'completed')
                ->whereNotNull('follow_up_instructions')
                ->where('completed_at', '>=', now()->subDays(14))
                ->latest('completed_at')
                ->limit(8)
                ->get(),
            'pendingLabs' => LabOrder::query()
                ->with(['patient', 'test', 'visit'])
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->whereHas('visit', fn ($visit) => $visit->where('doctor_id', $doctor->id))
                ->latest('id')
                ->limit(8)
                ->get(),
        ];
    }
}

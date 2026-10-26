<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $today = now()->toDateString();

        $todayAppointments = Appointment::query()
            ->whereDate('appointment_date', $today)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $pendingVisits = Appointment::query()
            ->where('status', 'pending')
            ->whereDate('appointment_date', '>=', $today)
            ->count();

        $unpaidBills = Bill::query()->unpaid()->count();
        $unreadMessages = $user?->can('content.manage')
            ? ContactMessage::query()->unread()->count()
            : 0;

        return view('admin.dashboard', [
            'deskSummary' => $this->deskSummary($user, $todayAppointments, $pendingVisits, $unpaidBills),
            'deskActions' => $this->deskActions($user),
            'needsYou' => $this->needsYou($user, $pendingVisits, $unpaidBills, $unreadMessages),
            'stats' => $this->stats($user, $todayAppointments, $pendingVisits, $unpaidBills, $unreadMessages),
            'upcomingAppointments' => $user?->can('appointments.view')
                ? Appointment::query()
                    ->with(['patient', 'doctor', 'department'])
                    ->whereDate('appointment_date', '>=', $today)
                    ->whereNotIn('status', ['cancelled', 'completed', 'no_show'])
                    ->orderBy('appointment_date')
                    ->orderBy('start_time')
                    ->orderBy('id')
                    ->limit(8)
                    ->get()
                : Collection::make(),
            'openBills' => $user?->can('billing.manage')
                ? Bill::query()
                    ->unpaid()
                    ->with('patient')
                    ->orderBy('bill_date')
                    ->orderBy('id')
                    ->limit(5)
                    ->get()
                : Collection::make(),
        ]);
    }

    /**
     * @return list<array{label: string, icon: string, href: string, primary: bool}>
     */
    private function deskActions(?User $user): array
    {
        $actions = [];

        if ($user?->can('billing.manage')) {
            $actions[] = [
                'label' => 'New bill',
                'icon' => 'receipt',
                'href' => route('admin.bills.create'),
                'primary' => true,
            ];
        }

        if ($user?->can('doctors.manage')) {
            $actions[] = [
                'label' => 'Add doctor',
                'icon' => 'stethoscope',
                'href' => route('admin.doctors.create'),
                'primary' => false,
            ];
        }

        return $actions;
    }

    /**
     * @return list<array{label: string, href: string}>
     */
    private function needsYou(?User $user, int $pendingVisits, int $unpaidBills, int $unreadMessages): array
    {
        $items = [];

        if ($user?->can('appointments.view') && $pendingVisits > 0) {
            $items[] = [
                'label' => $pendingVisits === 1 ? '1 visit waiting to confirm' : $pendingVisits.' visits waiting to confirm',
                'href' => route('admin.appointments.index', ['status' => 'pending']),
            ];
        }

        if ($user?->can('billing.manage') && $unpaidBills > 0) {
            $items[] = [
                'label' => $unpaidBills === 1 ? '1 unpaid bill' : $unpaidBills.' unpaid bills',
                'href' => route('admin.bills.index'),
            ];
        }

        if ($user?->can('content.manage') && $unreadMessages > 0) {
            $items[] = [
                'label' => $unreadMessages === 1 ? '1 unread website message' : $unreadMessages.' unread website messages',
                'href' => route('admin.contact-messages.index'),
            ];
        }

        return $items;
    }

    /**
     * @return list<array{label: string, value: int, note: string, icon: string, tone: string, href: string}>
     */
    private function stats(?User $user, int $todayAppointments, int $pendingVisits, int $unpaidBills, int $unreadMessages): array
    {
        $stats = [];

        if ($user?->can('appointments.view')) {
            $stats[] = [
                'label' => 'Today',
                'value' => $todayAppointments,
                'note' => 'Visits booked',
                'icon' => 'calendar',
                'tone' => 'blue',
                'href' => route('admin.appointments.index'),
            ];
            $stats[] = [
                'label' => 'Pending',
                'value' => $pendingVisits,
                'note' => 'Need confirmation',
                'icon' => 'clock',
                'tone' => 'rose',
                'href' => route('admin.appointments.index', ['status' => 'pending']),
            ];
        }

        if ($user?->can('patients.view')) {
            $stats[] = [
                'label' => 'Patients',
                'value' => Patient::query()->count(),
                'note' => 'On file',
                'icon' => 'users',
                'tone' => 'green',
                'href' => route('admin.patients.index'),
            ];
        }

        if ($user?->can('doctors.manage')) {
            $stats[] = [
                'label' => 'Doctors',
                'value' => Doctor::query()->active()->count(),
                'note' => 'Active now',
                'icon' => 'stethoscope',
                'tone' => 'purple',
                'href' => route('admin.doctors.index'),
            ];
            $stats[] = [
                'label' => 'Departments',
                'value' => Department::query()->active()->count(),
                'note' => 'Open units',
                'icon' => 'building',
                'tone' => 'orange',
                'href' => route('admin.departments.index'),
            ];
        }

        if ($user?->can('billing.manage')) {
            $stats[] = [
                'label' => 'Unpaid',
                'value' => $unpaidBills,
                'note' => 'Open bills',
                'icon' => 'receipt',
                'tone' => 'cyan',
                'href' => route('admin.bills.index'),
            ];
        }

        if ($user?->can('content.manage') && ! $user->can('appointments.view')) {
            $stats[] = [
                'label' => 'Messages',
                'value' => $unreadMessages,
                'note' => 'Unread mail',
                'icon' => 'mail',
                'tone' => 'blue',
                'href' => route('admin.contact-messages.index'),
            ];
        }

        return $stats;
    }

    private function deskSummary(?User $user, int $todayAppointments, int $pendingVisits, int $unpaidBills): string
    {
        $parts = [];

        if ($user?->can('appointments.view')) {
            $parts[] = $todayAppointments === 1 ? '1 visit today' : $todayAppointments.' visits today';

            if ($pendingVisits > 0) {
                $parts[] = $pendingVisits === 1 ? '1 still needs confirmation' : $pendingVisits.' still need confirmation';
            }
        }

        if ($user?->can('billing.manage') && $unpaidBills > 0) {
            $parts[] = $unpaidBills === 1 ? '1 unpaid bill' : $unpaidBills.' unpaid bills';
        }

        return $parts !== []
            ? implode(' · ', $parts)
            : 'Use the menu on the left to open a page.';
    }
}

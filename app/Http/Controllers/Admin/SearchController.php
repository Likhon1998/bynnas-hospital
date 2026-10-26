<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $term = trim($request->string('q')->toString());
        $user = $request->user();

        if ($term === '' || mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $like = '%'.$term.'%';
        $results = [];

        if ($user?->can('beds.view') || $user?->can('operations.manage')) {
            Bed::query()
                ->with(['ward', 'patient'])
                ->where(function ($query) use ($like): void {
                    $query->where('bed_code', 'like', $like)
                        ->orWhereHas('patient', fn ($patient) => $patient->where('full_name', 'like', $like));
                })
                ->orderBy('bed_code')
                ->limit(6)
                ->get()
                ->each(function (Bed $bed) use (&$results): void {
                    $patientName = $bed->patient?->full_name;
                    $results[] = [
                        'type' => 'Bed',
                        'title' => $bed->bed_code,
                        'detail' => trim(($bed->ward?->name ?: 'Ward').' · '.$bed->status.($patientName ? ' · '.$patientName : '')),
                        'href' => route('admin.beds.index', ['q' => $bed->bed_code]),
                    ];
                });
        }

        if ($user?->can('patients.view')) {
            Patient::query()
                ->where(function ($query) use ($like): void {
                    $query->where('full_name', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })
                ->latest('id')
                ->limit(6)
                ->get()
                ->each(function (Patient $patient) use (&$results): void {
                    $results[] = [
                        'type' => 'Patient',
                        'title' => $patient->full_name,
                        'detail' => $patient->phone ?: ($patient->email ?: 'Patient record'),
                        'href' => route('admin.patients.show', $patient),
                    ];
                });
        }

        if ($user?->can('appointments.view')) {
            Appointment::query()
                ->with(['patient', 'doctor'])
                ->where(function ($query) use ($like): void {
                    $query->where('appointment_no', 'like', $like)
                        ->orWhereHas('patient', fn ($patient) => $patient->where('full_name', 'like', $like))
                        ->orWhereHas('doctor', fn ($doctor) => $doctor->where('name', 'like', $like));
                })
                ->orderByDesc('appointment_date')
                ->limit(6)
                ->get()
                ->each(function (Appointment $appointment) use (&$results): void {
                    $results[] = [
                        'type' => 'Visit',
                        'title' => $appointment->appointment_no,
                        'detail' => trim(($appointment->patient?->full_name ?: 'Patient').' · '.$appointment->status),
                        'href' => route('admin.appointments.show', $appointment),
                    ];
                });
        }

        return response()->json([
            'results' => array_slice($results, 0, 12),
        ]);
    }
}

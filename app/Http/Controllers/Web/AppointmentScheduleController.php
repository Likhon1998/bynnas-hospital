<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Support\AppointmentDesk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentScheduleController extends Controller
{
    public function __invoke(Request $request, AppointmentDesk $desk): JsonResponse
    {
        $validated = $request->validate([
            'doctor' => ['required', 'integer', 'exists:doctors,id'],
            'view' => ['required', 'string', 'in:month,week'],
            'date' => ['nullable', 'date'],
        ]);

        $doctor = Doctor::query()
            ->active()
            ->with(['doctorSchedules', 'doctorLeaves'])
            ->find($validated['doctor']);

        if ($doctor === null) {
            return response()->json([
                'view' => $validated['view'],
                'start' => now()->toDateString(),
                'end' => now()->toDateString(),
                'label' => 'Unavailable',
                'clinic_days' => [],
                'weekdays' => [],
                'days' => [],
            ]);
        }

        return response()->json($desk->schedule(
            $doctor,
            $validated['view'],
            $validated['date'] ?? now()->toDateString(),
        ));
    }
}

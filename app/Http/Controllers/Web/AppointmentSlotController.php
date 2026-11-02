<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Support\AppointmentDesk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentSlotController extends Controller
{
    public function __invoke(Request $request, AppointmentDesk $desk): JsonResponse
    {
        $validated = $request->validate([
            'doctor' => ['required', 'integer', 'exists:doctors,id'],
            'date' => ['required', 'date'],
        ]);

        $doctor = Doctor::query()
            ->active()
            ->with(['doctorSchedules', 'doctorLeaves'])
            ->find($validated['doctor']);

        if ($doctor === null) {
            return response()->json([
                'available' => false,
                'reason' => 'This doctor is not available for booking.',
                'slots' => [],
                'clinic_days' => [],
            ]);
        }

        return response()->json($desk->availability($doctor, $validated['date']));
    }
}

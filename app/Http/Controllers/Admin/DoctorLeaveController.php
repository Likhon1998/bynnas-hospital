<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDoctorLeaveRequest;
use App\Models\Doctor;
use App\Models\DoctorLeave;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DoctorLeaveController extends Controller
{
    public function store(StoreDoctorLeaveRequest $request, Doctor $doctor): RedirectResponse
    {
        $doctor->doctorLeaves()->create([
            ...$request->safe()->only(['starts_on', 'ends_on', 'type', 'reason']),
            'is_active' => true,
            'created_by' => $request->user()?->id,
        ]);

        return DoctorScheduleController::redirectAfterChange(
            $request,
            $doctor,
            'Leave recorded. Website and desk booking will block those dates.',
        );
    }

    public function destroy(Request $request, Doctor $doctor, DoctorLeave $doctorLeave): RedirectResponse
    {
        abort_unless($doctorLeave->doctor_id === $doctor->id, 404);
        abort_unless($request->user()?->can('clinic-hours.manage', $doctor), 403);

        $doctorLeave->delete();

        return DoctorScheduleController::redirectAfterChange(
            $request,
            $doctor,
            'Leave removed. Those dates are open again.',
        );
    }
}

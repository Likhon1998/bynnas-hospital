<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDoctorLeaveRequest;
use App\Models\Doctor;
use App\Models\DoctorLeave;
use Illuminate\Http\RedirectResponse;

class DoctorLeaveController extends Controller
{
    public function store(StoreDoctorLeaveRequest $request, Doctor $doctor): RedirectResponse
    {
        $doctor->doctorLeaves()->create([
            ...$request->safe()->only(['starts_on', 'ends_on', 'type', 'reason']),
            'is_active' => true,
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.doctors.edit', $doctor)
            ->with('status', 'Leave recorded. Website and desk booking will block those dates.');
    }

    public function destroy(Doctor $doctor, DoctorLeave $doctorLeave): RedirectResponse
    {
        abort_unless($doctorLeave->doctor_id === $doctor->id, 404);

        $doctorLeave->delete();

        return redirect()
            ->route('admin.doctors.edit', $doctor)
            ->with('status', 'Leave removed. Those dates are open again.');
    }
}

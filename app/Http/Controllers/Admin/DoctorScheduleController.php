<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDoctorScheduleRequest;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DoctorScheduleController extends Controller
{
    public function edit(Request $request): View
    {
        $doctor = $request->user()?->doctor;

        abort_unless($doctor instanceof Doctor, 403);

        $doctor->load([
            'doctorLeaves' => fn ($query) => $query->orderByDesc('starts_on')->orderByDesc('id'),
            'doctorSchedules',
        ]);

        return view('admin.clinic-hours.edit', [
            'doctor' => $doctor,
            'hours' => DoctorSchedule::formStateFor($doctor),
        ]);
    }

    public function update(UpdateDoctorScheduleRequest $request, Doctor $doctor): RedirectResponse
    {
        DB::transaction(function () use ($doctor, $request): void {
            $doctor->doctorSchedules()->delete();

            foreach ($request->sessions() as $session) {
                $doctor->doctorSchedules()->create($session);
            }
        });

        return self::redirectAfterChange(
            $request,
            $doctor,
            'Clinic hours saved. The public calendar now uses these days and times.',
        );
    }

    public static function redirectAfterChange(Request $request, Doctor $doctor, string $status): RedirectResponse
    {
        if ($request->user()?->can('doctors.manage')) {
            return redirect()
                ->route('admin.doctors.edit', $doctor)
                ->with('status', $status);
        }

        return redirect()
            ->route('admin.clinic-hours.edit')
            ->with('status', $status);
    }
}

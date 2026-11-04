<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $searchedName = trim((string) $request->query('q', ''));
        $specialty = trim((string) $request->query('specialty', ''));
        $sort = trim((string) $request->query('sort', 'featured'));
        $featuredOnly = $request->boolean('featured');

        if (! in_array($sort, ['featured', 'name', 'experience', 'fee'], true)) {
            $sort = 'featured';
        }

        $departments = Department::query()
            ->active()
            ->withCount(['doctors as consultants_count' => fn (Builder $query) => $query->active()])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $doctorsQuery = Doctor::query()
            ->active()
            ->with(['department', 'doctorSchedules' => fn ($query) => $query->active()])
            ->when($searchedName !== '', function (Builder $query) use ($searchedName): void {
                $query->where(function (Builder $match) use ($searchedName): void {
                    $match->where('name', 'like', '%'.$searchedName.'%')
                        ->orWhere('specialization', 'like', '%'.$searchedName.'%')
                        ->orWhere('designation', 'like', '%'.$searchedName.'%')
                        ->orWhere('qualifications', 'like', '%'.$searchedName.'%');
                });
            })
            ->when($specialty !== '', function (Builder $query) use ($specialty): void {
                $query->where(function (Builder $match) use ($specialty): void {
                    $match->where('specialization', 'like', '%'.$specialty.'%')
                        ->orWhereHas('department', function (Builder $departmentQuery) use ($specialty): void {
                            $departmentQuery->where('name', $specialty);
                        });
                });
            })
            ->when($featuredOnly, fn (Builder $query) => $query->featured());

        $doctorsQuery = match ($sort) {
            'name' => $doctorsQuery->orderBy('name'),
            'experience' => $doctorsQuery->orderByDesc('experience_years')->orderBy('name'),
            'fee' => $doctorsQuery->orderBy('consultation_fee')->orderBy('name'),
            default => $doctorsQuery->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name'),
        };

        $doctors = $doctorsQuery->get();

        $isFiltered = $searchedName !== '' || $specialty !== '' || $featuredOnly;

        $featuredDoctors = collect();

        if (! $isFiltered) {
            $featuredDoctors = $doctors
                ->where('is_featured', true)
                ->take(6)
                ->values();
        }

        $groupedDoctors = $doctors
            ->groupBy(fn (Doctor $doctor): string => $doctor->department?->name ?: ($doctor->specialization ?: 'Consultants'))
            ->sortKeys(SORT_NATURAL | SORT_FLAG_CASE);

        $totalConsultants = Doctor::query()->active()->count();
        $clinicCount = $departments->where('consultants_count', '>', 0)->count();

        return view('web.doctors', [
            'doctors' => $doctors,
            'groupedDoctors' => $groupedDoctors,
            'featuredDoctors' => $featuredDoctors,
            'departments' => $departments,
            'specialty' => $specialty,
            'searchedName' => $searchedName,
            'sort' => $sort,
            'featuredOnly' => $featuredOnly,
            'isFiltered' => $isFiltered,
            'totalConsultants' => $totalConsultants,
            'clinicCount' => $clinicCount,
            'weekdayToday' => strtolower(Carbon::now()->englishDayOfWeek),
        ]);
    }

    public function show(Doctor $doctor): View
    {
        abort_unless($doctor->is_active, 404);

        $doctor->load([
            'department',
            'doctorSchedules' => fn ($query) => $query->active(),
        ]);

        $sessions = $doctor->doctorSchedules->keyBy('weekday');

        $clinicHours = collect(DoctorSchedule::WEEKDAYS)
            ->map(function (string $label, string $weekday) use ($sessions): array {
                $session = $sessions->get($weekday);

                return [
                    'label' => $label,
                    'open' => $session !== null,
                    'hours' => $session
                        ? $this->formatClock($session->start_time).' – '.$this->formatClock($session->end_time)
                        : 'Closed',
                ];
            })
            ->values();

        $relatedDoctors = Doctor::query()
            ->active()
            ->with('department')
            ->where('id', '!=', $doctor->id)
            ->when(
                $doctor->department_id,
                fn (Builder $query) => $query->where('department_id', $doctor->department_id),
            )
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(4)
            ->get();

        return view('web.doctor-show', [
            'doctor' => $doctor,
            'clinicHours' => $clinicHours,
            'hasClinicHours' => $sessions->isNotEmpty(),
            'relatedDoctors' => $relatedDoctors,
        ]);
    }

    private function formatClock(mixed $time): string
    {
        $value = substr((string) $time, 0, 5);

        if ($value === '' || preg_match('/^\d{2}:\d{2}$/', $value) !== 1) {
            return '';
        }

        return Carbon::createFromFormat('H:i', $value)->format('g:i A');
    }
}

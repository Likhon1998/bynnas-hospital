<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\HospitalSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $view = trim((string) $request->query('view', 'department'));

        if (! in_array($view, ['department', 'branch', 'name'], true)) {
            $view = 'department';
        }

        $searchedName = trim((string) $request->query('q', ''));
        $specialty = trim((string) $request->query('specialty', ''));
        $branch = trim((string) $request->query('branch', ''));
        $defaultSort = $view === 'name' ? 'name' : 'featured';
        $sort = trim((string) $request->query('sort', $defaultSort));
        $featuredOnly = $request->boolean('featured');

        if (! in_array($sort, ['featured', 'name', 'experience', 'fee'], true)) {
            $sort = $defaultSort;
        }

        $departments = Department::query()
            ->active()
            ->withCount(['doctors as consultants_count' => fn (Builder $query) => $query->active()])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $settings = HospitalSetting::map();
        $campusName = filled($settings->get('campus_name'))
            ? (string) $settings->get('campus_name')
            : 'Main Campus';
        $campusAddress = filled($settings->get('address'))
            ? (string) $settings->get('address')
            : 'Main Campus';
        $branches = collect([
            [
                'key' => Str::slug($campusName) ?: 'main-campus',
                'label' => $campusName,
                'address' => $campusAddress,
            ],
        ]);

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

        if ($view === 'branch' && $branch !== '' && $branch !== ($branches->first()['key'] ?? '')) {
            $doctorsQuery->whereRaw('1 = 0');
        }

        $doctorsQuery = match ($sort) {
            'name' => $doctorsQuery->orderBy('name'),
            'experience' => $doctorsQuery->orderByDesc('experience_years')->orderBy('name'),
            'fee' => $doctorsQuery->orderBy('consultation_fee')->orderBy('name'),
            default => $doctorsQuery->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name'),
        };

        $doctors = $doctorsQuery->get();

        $isFiltered = $searchedName !== '' || $specialty !== '' || $featuredOnly || $branch !== '';

        $groupedDoctors = $this->groupDoctors($doctors, $view, $campusName);

        return view('web.doctors', [
            'doctors' => $doctors,
            'groupedDoctors' => $groupedDoctors,
            'departments' => $departments,
            'branches' => $branches,
            'directoryView' => $view,
            'specialty' => $specialty,
            'branch' => $branch,
            'searchedName' => $searchedName,
            'sort' => $sort,
            'featuredOnly' => $featuredOnly,
            'isFiltered' => $isFiltered,
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

    /**
     * @param  Collection<int, Doctor>  $doctors
     * @return Collection<string, Collection<int, Doctor>>
     */
    private function groupDoctors(Collection $doctors, string $view, string $campusName): Collection
    {
        return match ($view) {
            'name' => $doctors
                ->groupBy(function (Doctor $doctor): string {
                    $letter = Str::upper(Str::substr(ltrim((string) $doctor->name), 0, 1));

                    return preg_match('/[A-Z]/', $letter) === 1 ? $letter : '#';
                })
                ->sortKeys(),
            'branch' => $doctors
                ->groupBy(fn (): string => $campusName)
                ->sortKeys(SORT_NATURAL | SORT_FLAG_CASE),
            default => $doctors
                ->groupBy(fn (Doctor $doctor): string => $doctor->department?->name ?: ($doctor->specialization ?: 'Consultants'))
                ->sortKeys(SORT_NATURAL | SORT_FLAG_CASE),
        };
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

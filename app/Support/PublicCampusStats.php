<?php

namespace App\Support;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Support\Str;

class PublicCampusStats
{
    /**
     * Live public counts from the published roster, not brochure numbers.
     *
     * @return array{
     *     doctors: int,
     *     departments: int,
     *     patients: int,
     *     services: int,
     *     summary: string,
     *     items: list<array{key: string, value: int, label: string}>
     * }
     */
    public function snapshot(): array
    {
        $doctors = Doctor::query()->active()->count();
        $departments = Department::query()->active()->count();
        $patients = Patient::query()->active()->count();
        $services = Service::query()->active()->count();

        return [
            'doctors' => $doctors,
            'departments' => $departments,
            'patients' => $patients,
            'services' => $services,
            'summary' => $this->summary($doctors, $departments),
            'items' => [
                ['key' => 'doctors', 'value' => $doctors, 'label' => 'Expert Doctors'],
                ['key' => 'departments', 'value' => $departments, 'label' => 'Specialties'],
                ['key' => 'patients', 'value' => $patients, 'label' => 'Patients'],
                ['key' => 'services', 'value' => $services, 'label' => 'Services'],
            ],
        ];
    }

    private function summary(int $doctors, int $departments): string
    {
        if ($doctors === 0) {
            return 'Choose a convenient time and specialist.';
        }

        return number_format($doctors).' '.Str::plural('consultant', $doctors)
            .' across '.number_format($departments).' '.Str::plural('specialty', $departments)
            .'. Pick a named doctor and a free slot.';
    }
}

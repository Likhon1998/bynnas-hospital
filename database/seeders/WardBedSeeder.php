<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\Patient;
use App\Models\Ward;
use Illuminate\Database\Seeder;

class WardBedSeeder extends Seeder
{
    public function run(): void
    {
        $wards = [
            ['name' => 'ICU A', 'code' => 'ICU-A', 'type' => 'icu', 'capacity' => 12, 'sort_order' => 1],
            ['name' => 'ICU B', 'code' => 'ICU-B', 'type' => 'icu', 'capacity' => 10, 'sort_order' => 2],
            ['name' => 'General 1', 'code' => 'GEN-1', 'type' => 'general', 'capacity' => 20, 'sort_order' => 3],
            ['name' => 'General 2', 'code' => 'GEN-2', 'type' => 'general', 'capacity' => 18, 'sort_order' => 4],
            ['name' => 'NICU', 'code' => 'NICU-1', 'type' => 'nicu', 'capacity' => 14, 'sort_order' => 5],
            ['name' => 'Isolation', 'code' => 'ISO-1', 'type' => 'isolation', 'capacity' => 8, 'sort_order' => 6],
        ];

        $patients = Patient::query()->active()->orderBy('id')->get();
        $patientIndex = 0;
        $assignedPatientIds = [];

        foreach ($wards as $wardData) {
            $ward = Ward::query()->updateOrCreate(
                ['code' => $wardData['code']],
                [
                    'name' => $wardData['name'],
                    'type' => $wardData['type'],
                    'capacity' => $wardData['capacity'],
                    'is_active' => true,
                    'sort_order' => $wardData['sort_order'],
                ]
            );

            for ($i = 1; $i <= $wardData['capacity']; $i++) {
                $bedCode = $ward->code.'-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                $roll = $i % 5;
                $status = match ($roll) {
                    0 => 'dirty',
                    1 => 'cleaning',
                    2, 3 => 'occupied',
                    default => 'available',
                };

                $patientId = null;
                $admittedAt = null;
                $statusChangedAt = now()->subHours(($i % 10) + 1);
                $turnoverHours = null;

                if ($status === 'occupied') {
                    while ($patientIndex < $patients->count() && in_array($patients[$patientIndex]->id, $assignedPatientIds, true)) {
                        $patientIndex++;
                    }

                    if ($patientIndex < $patients->count()) {
                        $patientId = $patients[$patientIndex]->id;
                        $assignedPatientIds[] = $patientId;
                        $patientIndex++;
                        $admittedAt = now()->subHours(($i * 3) % 72 + 4);
                        $statusChangedAt = $admittedAt;
                    } else {
                        $status = 'available';
                        $statusChangedAt = now()->subHours(2);
                    }
                }

                if ($status === 'dirty') {
                    $turnoverHours = round((($i % 7) + 12) / 5, 1);
                }

                Bed::query()->updateOrCreate(
                    [
                        'ward_id' => $ward->id,
                        'bed_code' => $bedCode,
                    ],
                    [
                        'patient_id' => $patientId,
                        'status' => $status,
                        'admitted_at' => $admittedAt,
                        'status_changed_at' => $statusChangedAt,
                        'turnover_hours' => $turnoverHours,
                    ]
                );
            }
        }
    }
}

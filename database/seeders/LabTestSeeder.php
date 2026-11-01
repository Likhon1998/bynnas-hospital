<?php

namespace Database\Seeders;

use App\Models\LabTest;
use Illuminate\Database\Seeder;

class LabTestSeeder extends Seeder
{
    public function run(): void
    {
        $tests = [
            [
                'code' => 'K',
                'name' => 'Serum Potassium',
                'category' => 'chemistry',
                'specimen' => 'blood',
                'unit' => 'mmol/L',
                'reference_range' => '3.5–5.1',
                'critical_low' => 2.5,
                'critical_high' => 6.5,
                'price' => 450,
            ],
            [
                'code' => 'GLU',
                'name' => 'Fasting Glucose',
                'category' => 'chemistry',
                'specimen' => 'blood',
                'unit' => 'mg/dL',
                'reference_range' => '70–99',
                'critical_low' => 54,
                'critical_high' => 400,
                'price' => 250,
            ],
            [
                'code' => 'TROP',
                'name' => 'Troponin I',
                'category' => 'cardiac',
                'specimen' => 'blood',
                'unit' => 'ng/mL',
                'reference_range' => '<0.04',
                'critical_low' => null,
                'critical_high' => 0.5,
                'price' => 1800,
            ],
            [
                'code' => 'CREAT',
                'name' => 'Serum Creatinine',
                'category' => 'chemistry',
                'specimen' => 'blood',
                'unit' => 'mg/dL',
                'reference_range' => '0.6–1.3',
                'critical_low' => null,
                'critical_high' => 4.0,
                'price' => 350,
            ],
            [
                'code' => 'HB',
                'name' => 'Hemoglobin',
                'category' => 'hematology',
                'specimen' => 'blood',
                'unit' => 'g/dL',
                'reference_range' => '12.0–16.5',
                'critical_low' => 7.0,
                'critical_high' => null,
                'price' => 200,
            ],
            [
                'code' => 'TSH',
                'name' => 'TSH',
                'category' => 'endocrine',
                'specimen' => 'blood',
                'unit' => 'mIU/L',
                'reference_range' => '0.4–4.0',
                'critical_low' => null,
                'critical_high' => null,
                'price' => 700,
            ],
            [
                'code' => 'NS1',
                'name' => 'Dengue NS1',
                'category' => 'serology',
                'specimen' => 'blood',
                'unit' => null,
                'reference_range' => 'Negative',
                'critical_low' => null,
                'critical_high' => null,
                'price' => 900,
            ],
            [
                'code' => 'URE',
                'name' => 'Urine Routine',
                'category' => 'urine',
                'specimen' => 'urine',
                'unit' => null,
                'reference_range' => 'See comment',
                'critical_low' => null,
                'critical_high' => null,
                'price' => 180,
            ],
        ];

        foreach ($tests as $test) {
            LabTest::query()->updateOrCreate(
                ['code' => $test['code']],
                [...$test, 'is_active' => true],
            );
        }
    }
}

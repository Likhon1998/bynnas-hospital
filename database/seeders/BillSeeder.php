<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\HealthPackage;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class BillSeeder extends Seeder
{
    /**
     * Development/demo bills. Safe to re-run.
     */
    public function run(): void
    {
        $receptionist = User::query()->where('email', 'receptionist@hospital.com')->first();
        $patient = Patient::query()->where('email', 'rahim.uddin.demo@example.test')->first();
        $appointment = Appointment::query()->with('doctor')->where('appointment_no', 'APT-DEMO-0001')->first();
        $package = HealthPackage::query()->orderBy('id')->first();

        if (! $patient) {
            return;
        }

        $bill = Bill::query()->updateOrCreate(
            ['bill_no' => 'BILL-DEMO-0001'],
            [
                'patient_id' => $patient->id,
                'appointment_id' => $appointment?->id,
                'issued_by' => $receptionist?->id,
                'bill_date' => now()->toDateString(),
                'status' => 'issued',
                'discount' => 0,
                'notes' => 'Demo reception bill for local testing only.',
                'issued_at' => now(),
                'paid_amount' => 0,
            ],
        );

        $items = [
            [
                'item_type' => 'consultation',
                'item_id' => $appointment?->doctor_id,
                'description' => 'Consultation — '.$appointment?->doctor?->name,
                'quantity' => 1,
                'unit_price' => $appointment?->doctor?->consultation_fee ?? 800,
            ],
        ];

        if ($package) {
            $items[] = [
                'item_type' => 'package',
                'item_id' => $package->id,
                'description' => $package->title,
                'quantity' => 1,
                'unit_price' => $package->price,
            ];
        }

        $bill->syncItems($items);
        $bill->update(['status' => 'issued', 'issued_at' => $bill->issued_at ?? now()]);
    }
}

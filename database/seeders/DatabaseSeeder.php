<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with development/demo hospital data.
     */
    public function run(): void
    {
        $this->call([
            RoleAndUserSeeder::class,
            HospitalSettingSeeder::class,
            DepartmentSeeder::class,
            DoctorSeeder::class,
            ServiceSeeder::class,
            HealthPackageSeeder::class,
            BlogSeeder::class,
            WebsiteContentSeeder::class,
            PatientAndAppointmentSeeder::class,
            BillSeeder::class,
        ]);
    }
}

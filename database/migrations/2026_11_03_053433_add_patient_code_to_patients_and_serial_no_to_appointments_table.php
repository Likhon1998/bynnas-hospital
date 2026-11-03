<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->string('patient_code', 32)->nullable()->unique()->after('id');
        });

        $patients = DB::table('patients')->orderBy('id')->get(['id']);

        foreach ($patients as $patient) {
            DB::table('patients')->where('id', $patient->id)->update([
                'patient_code' => 'PAT-'.str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT),
            ]);
        }

        Schema::table('appointments', function (Blueprint $table): void {
            $table->unsignedInteger('serial_no')->nullable()->after('appointment_no');
            $table->index(['doctor_id', 'appointment_date', 'serial_no']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropIndex(['doctor_id', 'appointment_date', 'serial_no']);
            $table->dropColumn('serial_no');
        });

        Schema::table('patients', function (Blueprint $table): void {
            $table->dropUnique(['patient_code']);
            $table->dropColumn('patient_code');
        });
    }
};

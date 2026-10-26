<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            $table->timestamp('status_changed_at')->nullable()->after('admitted_at');
        });

        DB::table('beds')->whereNull('status_changed_at')->update([
            'status_changed_at' => DB::raw('COALESCE(admitted_at, updated_at, created_at)'),
        ]);

        $duplicatePatientIds = DB::table('beds')
            ->select('patient_id')
            ->whereNotNull('patient_id')
            ->groupBy('patient_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('patient_id');

        foreach ($duplicatePatientIds as $patientId) {
            $keepId = DB::table('beds')
                ->where('patient_id', $patientId)
                ->orderBy('id')
                ->value('id');

            DB::table('beds')
                ->where('patient_id', $patientId)
                ->where('id', '!=', $keepId)
                ->update([
                    'patient_id' => null,
                    'status' => 'dirty',
                    'admitted_at' => null,
                    'status_changed_at' => now(),
                ]);
        }

        DB::table('beds')
            ->where('status', 'occupied')
            ->whereNull('patient_id')
            ->update([
                'status' => 'available',
                'admitted_at' => null,
                'status_changed_at' => now(),
            ]);

        DB::table('beds')
            ->where('status', '!=', 'occupied')
            ->whereNotNull('patient_id')
            ->update([
                'patient_id' => null,
                'admitted_at' => null,
            ]);

        Schema::table('beds', function (Blueprint $table) {
            $table->unique('patient_id');
        });
    }

    public function down(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            $table->dropUnique(['patient_id']);
            $table->dropColumn('status_changed_at');
        });
    }
};

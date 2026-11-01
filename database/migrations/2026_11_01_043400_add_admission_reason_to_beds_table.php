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
            $table->string('admission_reason')->nullable()->after('admitted_at');
        });

        $reasons = [
            'Respiratory support',
            'Cardiac monitoring',
            'Post-operative recovery',
            'Sepsis / shock',
            'Neuro observation',
        ];

        $occupied = DB::table('beds')->where('status', 'occupied')->orderBy('id')->get(['id']);

        foreach ($occupied as $index => $bed) {
            DB::table('beds')->where('id', $bed->id)->update([
                'admission_reason' => $reasons[$index % count($reasons)],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            $table->dropColumn('admission_reason');
        });
    }
};

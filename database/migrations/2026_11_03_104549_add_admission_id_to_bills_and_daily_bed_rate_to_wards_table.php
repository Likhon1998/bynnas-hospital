<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table): void {
            $table->foreignId('admission_id')
                ->nullable()
                ->after('appointment_id')
                ->constrained('admissions')
                ->nullOnDelete();
            $table->index(['admission_id', 'bill_date']);
        });

        Schema::table('wards', function (Blueprint $table): void {
            $table->decimal('daily_bed_rate', 10, 2)->nullable()->after('capacity');
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('admission_id');
        });

        Schema::table('wards', function (Blueprint $table): void {
            $table->dropColumn('daily_bed_rate');
        });
    }
};

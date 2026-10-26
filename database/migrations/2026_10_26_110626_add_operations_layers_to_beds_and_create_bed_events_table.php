<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            $table->boolean('is_maintenance')->default(false)->after('turnover_hours');
            $table->string('maintenance_reason')->nullable()->after('is_maintenance');
            $table->timestamp('maintenance_started_at')->nullable()->after('maintenance_reason');
            $table->foreignId('reserved_patient_id')->nullable()->after('maintenance_started_at')->constrained('patients')->nullOnDelete();
            $table->timestamp('reserved_until')->nullable()->after('reserved_patient_id');
            $table->string('reservation_note')->nullable()->after('reserved_until');
            $table->foreignId('reserved_by')->nullable()->after('reservation_note')->constrained('users')->nullOnDelete();
            $table->timestamp('reserved_at')->nullable()->after('reserved_by');

            $table->index(['status', 'is_maintenance']);
            $table->index(['reserved_patient_id', 'reserved_until']);
        });

        Schema::create('bed_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bed_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 64);
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32)->nullable();
            $table->foreignId('related_bed_id')->nullable()->constrained('beds')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['bed_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
        });

        Schema::create('bed_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_bed_id')->constrained('beds')->cascadeOnDelete();
            $table->foreignId('to_bed_id')->constrained('beds')->cascadeOnDelete();
            $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['patient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bed_transfers');
        Schema::dropIfExists('bed_events');

        Schema::table('beds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reserved_by');
            $table->dropConstrainedForeignId('reserved_patient_id');
            $table->dropIndex(['status', 'is_maintenance']);
            $table->dropColumn([
                'is_maintenance',
                'maintenance_reason',
                'maintenance_started_at',
                'reserved_until',
                'reservation_note',
                'reserved_at',
            ]);
        });
    }
};

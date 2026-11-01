<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_no', 30)->unique();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('waiting');
            $table->string('chief_complaint', 500)->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('triage_started_at')->nullable();
            $table->timestamp('consultation_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('blood_pressure', 20)->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->unsignedSmallInteger('pulse')->nullable();
            $table->unsignedTinyInteger('oxygen_saturation')->nullable();
            $table->decimal('weight', 5, 1)->nullable();
            $table->unsignedTinyInteger('pain_score')->nullable();
            $table->timestamp('vitals_recorded_at')->nullable();
            $table->foreignId('vitals_recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('symptoms')->nullable();
            $table->text('examination_notes')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('prescriptions')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->timestamp('clinically_recorded_at')->nullable();
            $table->foreignId('clinically_recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'arrived_at']);
            $table->index(['patient_id', 'status']);
            $table->index('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};

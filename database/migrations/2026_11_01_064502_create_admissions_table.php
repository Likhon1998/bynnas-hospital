<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->string('admission_no', 30)->unique();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->string('source', 20);
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('attending_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bed_id')->nullable()->constrained()->nullOnDelete();
            $table->string('admission_reason', 500);
            $table->string('status', 30)->default('admitted');
            $table->foreignId('admitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('admitted_at')->nullable();
            $table->foreignId('authorized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('authorized_at')->nullable();
            $table->foreignId('discharged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('discharged_at')->nullable();
            $table->text('discharge_summary')->nullable();
            $table->text('discharge_medications')->nullable();
            $table->text('follow_up_plan')->nullable();
            $table->json('outstanding_bills')->nullable();
            $table->boolean('billing_reviewed')->default(false);
            $table->timestamps();

            $table->index(['patient_id', 'status']);
            $table->index(['bed_id', 'status']);
            $table->index(['status', 'admitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};

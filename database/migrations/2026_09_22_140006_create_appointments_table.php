<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_no', 30)->unique();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('reason', 500)->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->string('source', 20)->default('web');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->char('confirmation_token', 36)->unique();
            $table->string('booking_key', 64)->nullable()->unique();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'appointment_date', 'status']);
            $table->index(['patient_id', 'appointment_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

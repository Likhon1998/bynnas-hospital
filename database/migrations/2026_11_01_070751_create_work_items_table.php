<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_items', function (Blueprint $table) {
            $table->id();
            $table->string('work_key', 80)->unique();
            $table->string('desk', 30);
            $table->string('kind', 40);
            $table->string('title');
            $table->string('detail', 500)->nullable();
            $table->string('href');
            $table->string('tone', 20)->default('orange');
            $table->string('source_type', 40);
            $table->unsignedBigInteger('source_id');
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('audience_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('audience_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->boolean('is_critical')->default(false);
            $table->boolean('is_notification')->default(false);
            $table->string('status', 20)->default('open');
            $table->timestamp('read_at')->nullable();
            $table->foreignId('read_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('escalated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['desk', 'status', 'kind']);
            $table->index(['status', 'is_notification', 'read_at']);
            $table->index(['audience_doctor_id', 'status']);
            $table->index(['source_type', 'source_id']);
            $table->index(['is_critical', 'status', 'escalated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_items');
    }
};

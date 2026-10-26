<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bed_code', 32);
            $table->string('status', 32)->default('available');
            $table->timestamp('admitted_at')->nullable();
            $table->decimal('turnover_hours', 5, 1)->nullable();
            $table->timestamps();

            $table->unique(['ward_id', 'bed_code']);
            $table->index(['status', 'ward_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};

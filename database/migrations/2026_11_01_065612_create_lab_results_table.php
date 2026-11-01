<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('visit_id')->constrained()->restrictOnDelete();
            $table->string('value', 80);
            $table->decimal('numeric_value', 12, 3)->nullable();
            $table->string('unit', 40)->nullable();
            $table->string('reference_range', 80)->nullable();
            $table->boolean('is_critical')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('verified_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('verified_at');
            $table->timestamps();

            $table->index(['patient_id', 'verified_at']);
            $table->index(['visit_id', 'is_critical']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};

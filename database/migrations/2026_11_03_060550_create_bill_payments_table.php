<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method', 20);
            $table->string('reference', 80)->nullable();
            $table->string('notes', 255)->nullable();
            $table->foreignId('collected_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('collected_at');
            $table->timestamps();

            $table->index(['bill_id', 'collected_at']);
            $table->index(['method', 'collected_at']);
            $table->index(['collected_by', 'collected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
    }
};

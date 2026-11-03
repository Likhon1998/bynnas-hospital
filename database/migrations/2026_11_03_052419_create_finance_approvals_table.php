<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_approvals', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 32);
            $table->string('status', 32)->default('pending');
            $table->foreignId('bill_id')->nullable()->constrained('bills')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_approvals');
    }
};

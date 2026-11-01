<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursing_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained()->restrictOnDelete();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('shift', 20);
            $table->string('incoming_shift', 20);
            $table->text('summary');
            $table->text('outstanding_work')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('handed_over_at');
            $table->timestamps();

            $table->index(['ward_id', 'handed_over_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nursing_handovers');
    }
};

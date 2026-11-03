<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_orders', function (Blueprint $table) {
            $table->string('sample_id', 40)->nullable()->unique()->after('clinical_notes');
            $table->string('barcode', 80)->nullable()->after('sample_id');
            $table->timestamp('submitted_for_verification_at')->nullable()->after('processing_by');
            $table->foreignId('submitted_for_verification_by')->nullable()->after('submitted_for_verification_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lab_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('submitted_for_verification_by');
            $table->dropColumn(['sample_id', 'barcode', 'submitted_for_verification_at']);
        });
    }
};

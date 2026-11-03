<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_results', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
        });

        Schema::table('lab_results', function (Blueprint $table) {
            $table->foreignId('verified_by')->nullable()->change();
            $table->timestamp('verified_at')->nullable()->change();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lab_results', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
        });

        Schema::table('lab_results', function (Blueprint $table) {
            $table->foreignId('verified_by')->nullable(false)->change();
            $table->timestamp('verified_at')->nullable(false)->change();
            $table->foreign('verified_by')->references('id')->on('users')->restrictOnDelete();
        });
    }
};

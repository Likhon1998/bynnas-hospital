<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            $table->unsignedInteger('lock_version')->default(0)->after('reserved_at');
        });
    }

    public function down(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            $table->dropColumn('lock_version');
        });
    }
};

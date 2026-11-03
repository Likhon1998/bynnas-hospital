<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_results', function (Blueprint $table) {
            $table->boolean('is_abnormal')->default(false)->after('is_critical');
            $table->text('findings')->nullable()->after('notes');
            $table->string('signature_name', 120)->nullable()->after('verified_at');
            $table->string('signature_title', 120)->nullable()->after('signature_name');
            $table->timestamp('published_at')->nullable()->after('signature_title');
        });
    }

    public function down(): void
    {
        Schema::table('lab_results', function (Blueprint $table) {
            $table->dropColumn([
                'is_abnormal',
                'findings',
                'signature_name',
                'signature_title',
                'published_at',
            ]);
        });
    }
};

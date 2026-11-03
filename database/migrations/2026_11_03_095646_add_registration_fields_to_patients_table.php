<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->string('nid_number', 50)->nullable()->after('email');
            $table->string('secondary_phone', 20)->nullable()->after('phone');
            $table->string('blood_group', 10)->nullable()->after('gender');
            $table->string('marital_status', 20)->nullable()->after('blood_group');
            $table->string('occupation', 120)->nullable()->after('marital_status');
            $table->string('guardian_name')->nullable()->after('occupation');
            $table->string('district', 120)->nullable()->after('address');
            $table->string('emergency_contact_relation', 40)->nullable()->after('emergency_contact_name');
            $table->text('notes')->nullable()->after('allergies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropColumn([
                'nid_number',
                'secondary_phone',
                'blood_group',
                'marital_status',
                'occupation',
                'guardian_name',
                'district',
                'emergency_contact_relation',
                'notes',
            ]);
        });
    }
};

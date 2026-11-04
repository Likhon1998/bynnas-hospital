<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('board_members', function (Blueprint $table): void {
            $table->string('role', 40)->default('director')->after('title');
            $table->string('slug')->nullable()->after('name');
            $table->json('achievements')->nullable()->after('bio');
        });

        $used = [];

        DB::table('board_members')->orderBy('id')->get(['id', 'name', 'title'])->each(function (object $member) use (&$used): void {
            $base = Str::slug((string) $member->name) ?: 'leader-'.$member->id;
            $slug = $base;
            $i = 2;

            while (in_array($slug, $used, true)) {
                $slug = $base.'-'.$i;
                $i++;
            }

            $used[] = $slug;

            $title = Str::lower((string) $member->title);
            $role = match (true) {
                str_contains($title, 'founder') && str_contains($title, 'co') => 'co_founder',
                str_contains($title, 'founder') => 'founder',
                str_contains($title, 'chairman') || str_contains($title, 'chairperson') => 'chairman',
                str_contains($title, 'managing director') => 'managing_director',
                str_contains($title, 'advisor') => 'advisor',
                default => 'director',
            };

            DB::table('board_members')->where('id', $member->id)->update([
                'slug' => $slug,
                'role' => $role,
            ]);
        });

        Schema::table('board_members', function (Blueprint $table): void {
            $table->unique('slug');
            $table->index(['role', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('board_members', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropIndex(['role', 'is_active', 'sort_order']);
            $table->dropColumn(['role', 'slug', 'achievements']);
        });
    }
};

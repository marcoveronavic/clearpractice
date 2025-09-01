<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practices', function (Blueprint $table) {
            if (! Schema::hasColumn('practices', 'slug')) {
                $table->string('slug')->nullable()->unique();
            }
        });

        // Backfill slugs for existing rows
        $rows = DB::table('practices')->whereNull('slug')->get();
        foreach ($rows as $row) {
            $base = Str::slug($row->name);
            $slug = $base ?: 'practice-'.$row->id;

            // ensure uniqueness
            $i = 1;
            while (DB::table('practices')->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            DB::table('practices')->where('id', $row->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('practices', 'slug')) {
            Schema::table('practices', function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            });
        }
    }
};


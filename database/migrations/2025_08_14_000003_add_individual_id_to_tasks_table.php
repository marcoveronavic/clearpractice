<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Only add the column if the tasks table actually exists.
        if (Schema::hasTable('tasks') && !Schema::hasColumn('tasks', 'individual_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                // keep it simple and portable (no FK so SQLite won't complain)
                $table->unsignedBigInteger('individual_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Only drop if both the table and column exist.
        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'individual_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('individual_id');
            });
        }
    }
};


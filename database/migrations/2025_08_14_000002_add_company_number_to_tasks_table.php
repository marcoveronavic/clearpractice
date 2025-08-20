<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Only add the column if the tasks table actually exists.
        if (Schema::hasTable('tasks') && !Schema::hasColumn('tasks', 'company_number')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->string('company_number')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Only try to drop if both the table and column exist.
        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'company_number')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('company_number');
            });
        }
    }
};

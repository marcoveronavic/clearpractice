<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'practice_slug')) {
                $table->string('practice_slug')->unique()->nullable()->after('practice');
            }
            if (!Schema::hasColumn('leads', 'home_path')) {
                $table->string('home_path')->nullable()->after('practice_slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'home_path'))   $table->dropColumn('home_path');
            if (Schema::hasColumn('leads', 'practice_slug')) $table->dropColumn('practice_slug');
        });
    }
};

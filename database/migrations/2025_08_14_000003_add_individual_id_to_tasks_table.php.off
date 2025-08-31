<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'individual_id')) {
                $table->foreignId('individual_id')
                    ->nullable()
                    ->constrained('individuals')
                    ->nullOnDelete()
                    ->after('company_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'individual_id')) {
                $table->dropConstrainedForeignId('individual_id');
            }
        });
    }
};

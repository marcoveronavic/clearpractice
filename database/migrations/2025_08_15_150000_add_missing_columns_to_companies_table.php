<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Add columns only if they are missing (safe to run multiple times)
            if (!Schema::hasColumn('companies', 'date_of_creation')) {
                $table->date('date_of_creation')->nullable();
            }
            if (!Schema::hasColumn('companies', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('companies', 'data')) {
                $table->json('data')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'data')) {
                $table->dropColumn('data');
            }
            if (Schema::hasColumn('companies', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('companies', 'date_of_creation')) {
                $table->dropColumn('date_of_creation');
            }
        });
    }
};


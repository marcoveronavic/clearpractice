<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'date_of_creation')) {
                $table->date('date_of_creation')->nullable()->after('status');
            }
        });
    }
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'date_of_creation')) {
                $table->dropColumn('date_of_creation');
            }
        });
    }
};

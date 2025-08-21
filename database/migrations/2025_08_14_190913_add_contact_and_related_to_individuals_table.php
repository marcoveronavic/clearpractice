<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('individuals', function (Blueprint $table) {
            if (!Schema::hasColumn('individuals', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('individuals', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            // Store as JSON text (works on sqlite/mysql/pgsql)
            if (!Schema::hasColumn('individuals', 'related_companies')) {
                $table->text('related_companies')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('individuals', function (Blueprint $table) {
            if (Schema::hasColumn('individuals', 'related_companies')) {
                $table->dropColumn('related_companies');
            }
            if (Schema::hasColumn('individuals', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('individuals', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};

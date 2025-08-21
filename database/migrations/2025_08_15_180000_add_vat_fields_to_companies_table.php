<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'vat_number'))   $table->string('vat_number')->nullable()->after('address');
            if (!Schema::hasColumn('companies', 'utr'))          $table->string('utr')->nullable()->after('vat_number');
            if (!Schema::hasColumn('companies', 'auth_code'))    $table->string('auth_code')->nullable()->after('utr');
            if (!Schema::hasColumn('companies', 'vat_period'))   $table->string('vat_period')->nullable()->after('auth_code'); // 'monthly' or 'quarterly'
            if (!Schema::hasColumn('companies', 'vat_quarter'))  $table->string('vat_quarter')->nullable()->after('vat_period'); // 'jan_apr_jul_oct'|'feb_may_nov'|'mar_jun_sep_dec'
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'vat_quarter')) $table->dropColumn('vat_quarter');
            if (Schema::hasColumn('companies', 'vat_period'))  $table->dropColumn('vat_period');
            if (Schema::hasColumn('companies', 'auth_code'))   $table->dropColumn('auth_code');
            if (Schema::hasColumn('companies', 'utr'))         $table->dropColumn('utr');
            if (Schema::hasColumn('companies', 'vat_number'))  $table->dropColumn('vat_number');
        });
    }
};


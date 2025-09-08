<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'vat_number'))          $table->string('vat_number', 20)->nullable();
            if (!Schema::hasColumn('companies', 'utr'))                 $table->string('utr', 20)->nullable();
            if (!Schema::hasColumn('companies', 'authentication_code')) $table->string('authentication_code', 50)->nullable();
            if (!Schema::hasColumn('companies', 'vat_period'))          $table->string('vat_period', 20)->nullable();
            if (!Schema::hasColumn('companies', 'vat_quarter_end'))     $table->string('vat_quarter_end', 20)->nullable();
            if (!Schema::hasColumn('companies', 'telephone'))           $table->string('telephone', 50)->nullable();
            if (!Schema::hasColumn('companies', 'email'))               $table->string('email', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            foreach (['vat_number','utr','authentication_code','vat_period','vat_quarter_end','telephone','email'] as $col) {
                if (Schema::hasColumn('companies', $col)) $table->dropColumn($col);
            }
        });
    }
};


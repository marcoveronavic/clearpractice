<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // key fields
            $table->string('number', 32)->unique();
            $table->string('name', 255);
            $table->string('status', 50)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('created', 50)->nullable();

            // details
            $table->text('address')->nullable();
            $table->json('sic_codes')->nullable();

            // deadlines/info from CH
            $table->json('accounts')->nullable();
            $table->json('confirmation_statement')->nullable();

            // editable fields in the UI
            $table->string('vat_number', 50)->nullable();
            $table->string('authentication_code', 50)->nullable();
            $table->string('utr', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('telephone', 50)->nullable();

            // VAT settings
            $table->string('vat_period', 20)->nullable();          // monthly | quarterly
            $table->string('vat_quarter_group', 30)->nullable();   // Jan/Apr/Jul/Oct etc.

            // full CH payload (for officers/pscs etc.)
            $table->json('raw')->nullable();

            $table->timestamp('saved_at')->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

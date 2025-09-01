<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_number')->nullable();
            $table->string('company_name')->nullable();
            $table->string('source')->default('companies_house');
            $table->json('raw_json')->nullable();
            $table->timestamps();

            $table->index(['company_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};


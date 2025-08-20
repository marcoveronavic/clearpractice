<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create only if missing to avoid "table already exists" errors
        if (! Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('number');
                $table->string('name');
                $table->string('status')->nullable();
                $table->date('date_of_creation')->nullable();
                $table->string('address')->nullable();
                $table->text('data')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('companies')) {
            Schema::drop('companies');
        }
    }
};

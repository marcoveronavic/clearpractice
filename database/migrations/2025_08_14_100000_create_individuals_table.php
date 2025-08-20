<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Only create if it does NOT already exist.
        if (! Schema::hasTable('individuals')) {
            Schema::create('individuals', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email');
                $table->string('phone')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Rollback ONLY if the table exists.
        if (Schema::hasTable('individuals')) {
            Schema::drop('individuals');
        }
    }
};

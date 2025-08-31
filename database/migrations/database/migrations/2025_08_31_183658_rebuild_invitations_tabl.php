<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop any broken version of the table
        Schema::dropIfExists('invitations');

        // Recreate with correct schema (SQLite‑friendly)
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();

            // keep it simple for SQLite; we’ll index it instead of adding FK constraint
            $table->unsignedBigInteger('practice_id');
            $table->index('practice_id');

            $table->string('first_name', 255);
            $table->string('surname', 255);
            $table->string('email', 255);

            $table->string('role', 50)->default('member');
            $table->string('token', 100)->unique();

            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();

            $table->timestamps();

            $table->index(['practice_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};


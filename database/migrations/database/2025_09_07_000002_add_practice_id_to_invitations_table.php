<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If the table doesn't exist yet, create it fully (fallback).
        if (! Schema::hasTable('invitations')) {
            Schema::create('invitations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('practice_id'); // keep it simple for SQLite
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

            return;
        }

        // Otherwise just add the missing column.
        if (! Schema::hasColumn('invitations', 'practice_id')) {
            Schema::table('invitations', function (Blueprint $table) {
                $table->unsignedBigInteger('practice_id')->nullable(); // SQLite-friendly alter
                $table->index('practice_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invitations') && Schema::hasColumn('invitations', 'practice_id')) {
            Schema::table('invitations', function (Blueprint $table) {
                $table->dropIndex(['practice_id']);
                $table->dropColumn('practice_id');
            });
        }
    }
};
<?php

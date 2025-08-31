<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();

            // Which practice this invite is for
            $table->foreignId('practice_id')
                ->constrained()
                ->cascadeOnDelete();

            // Invitee identity
            $table->string('first_name', 255);
            $table->string('surname', 255);
            $table->string('email', 255);

            // Role they’ll get when they join
            $table->string('role', 50)->default('member');

            // Unique token used in the accept link
            $table->string('token', 100)->unique();

            // Lifecycle
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();

            $table->timestamps();

            // Helpful index
            $table->index(['practice_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};


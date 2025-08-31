<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('practice_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_id')->constrained('practices')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 32)->default('member'); // 'admin' or 'member'
            $table->timestamps();

            $table->unique(['practice_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_user');
    }
};

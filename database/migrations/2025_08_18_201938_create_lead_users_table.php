<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lead_users', function (Blueprint $table) {
            $table->id();

            // FK to leads.id (this is the column that was missing)
            $table->unsignedBigInteger('lead_id');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->timestamps();

            $table->foreign('lead_id')
                ->references('id')->on('leads')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_users');
    }
};


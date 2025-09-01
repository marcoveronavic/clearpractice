<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deadlines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('type'); // 'accounts' | 'confirmation_statement'
            $table->date('period_start_on')->nullable();
            $table->date('period_end_on')->nullable();
            $table->date('due_on');
            $table->date('filed_on')->nullable(); // when that period was actually filed (if historical)
            $table->string('status')->default('upcoming'); // upcoming | overdue | filed_on_time | filed_late
            $table->string('source')->default('companies_house');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unique(['company_id', 'type', 'period_end_on', 'due_on'], 'uniq_deadline_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deadlines');
    }
};


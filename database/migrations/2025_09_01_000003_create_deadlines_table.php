<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('deadlines')) {
            Schema::create('deadlines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('type'); // 'accounts' | 'confirmation_statement'
                $table->date('period_start_on')->nullable();
                $table->date('period_end_on')->nullable();
                $table->date('due_on')->nullable();
                $table->date('filed_on')->nullable();
                $table->string('status')->default('upcoming'); // upcoming | overdue | filed_late | filed_on_time
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                $table->index(['company_id', 'type']);
                $table->index('due_on');
                $table->index('period_end_on');
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasTable('deadlines')) {
            Schema::drop('deadlines');
        }
    }
};

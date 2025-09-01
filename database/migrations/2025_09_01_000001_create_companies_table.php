<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_number')->unique();
            $table->string('name');
            $table->string('status')->nullable();
            $table->string('company_type')->nullable();
            $table->date('date_of_creation')->nullable();

            // Convenience fields from profile
            $table->date('accounts_next_due')->nullable();
            $table->date('accounts_next_period_end_on')->nullable();
            $table->boolean('accounts_overdue')->default(false);

            $table->date('confirmation_next_due')->nullable();
            $table->date('confirmation_next_made_up_to')->nullable();
            $table->boolean('confirmation_overdue')->default(false);

            $table->json('registered_office_address')->nullable();
            $table->longText('raw_profile_json')->nullable(); // entire CH profile payload (debug/trace)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};


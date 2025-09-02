<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create the table if it doesn't exist
        if (! Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('company_number')->index();      // use index (unique is tricky to add later on SQLite)
                $table->string('name');
                $table->string('status')->nullable();
                $table->string('company_type')->nullable();
                $table->date('date_of_creation')->nullable();

                $table->date('accounts_next_due')->nullable();
                $table->date('accounts_next_period_end_on')->nullable();
                $table->boolean('accounts_overdue')->default(false);

                $table->date('confirmation_next_due')->nullable();
                $table->date('confirmation_next_made_up_to')->nullable();
                $table->boolean('confirmation_overdue')->default(false);

                $table->text('registered_office_address')->nullable();
                $table->json('raw_profile_json')->nullable();    // TEXT on SQLite

                $table->timestamps();

                $table->index('accounts_next_due');
                $table->index('confirmation_next_due');
            });
            return;
        }

        // Otherwise: patch in any missing columns (SQLite-safe)
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'company_number'))             $table->string('company_number')->nullable()->index()->after('id');
            if (! Schema::hasColumn('companies', 'name'))                       $table->string('name')->nullable();
            if (! Schema::hasColumn('companies', 'status'))                     $table->string('status')->nullable();
            if (! Schema::hasColumn('companies', 'company_type'))               $table->string('company_type')->nullable();
            if (! Schema::hasColumn('companies', 'date_of_creation'))           $table->date('date_of_creation')->nullable();

            if (! Schema::hasColumn('companies', 'accounts_next_due'))          $table->date('accounts_next_due')->nullable();
            if (! Schema::hasColumn('companies', 'accounts_next_period_end_on'))$table->date('accounts_next_period_end_on')->nullable();
            if (! Schema::hasColumn('companies', 'accounts_overdue'))           $table->boolean('accounts_overdue')->default(false);

            if (! Schema::hasColumn('companies', 'confirmation_next_due'))      $table->date('confirmation_next_due')->nullable();
            if (! Schema::hasColumn('companies', 'confirmation_next_made_up_to'))$table->date('confirmation_next_made_up_to')->nullable();
            if (! Schema::hasColumn('companies', 'confirmation_overdue'))       $table->boolean('confirmation_overdue')->default(false);

            if (! Schema::hasColumn('companies', 'registered_office_address'))  $table->text('registered_office_address')->nullable();
            if (! Schema::hasColumn('companies', 'raw_profile_json'))           $table->json('raw_profile_json')->nullable();

            if (! Schema::hasColumn('companies', 'created_at'))                 $table->timestamps(); // adds created_at & updated_at
        });
    }

    public function down(): void
    {
        // Do not drop the table to preserve data; no-op on rollback.
    }
};

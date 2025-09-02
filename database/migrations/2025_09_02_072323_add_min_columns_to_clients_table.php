<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMinColumnsToClientsTable extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clients')) {
            // The create_clients_table migration handles table creation.
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            // At least one visible “name” field
            if (! Schema::hasColumn('clients', 'name') &&
                ! Schema::hasColumn('clients', 'company_name')) {
                $table->string('name')->nullable();
            }

            if (! Schema::hasColumn('clients', 'company_name')) {
                $table->string('company_name')->nullable();
            }

            if (! Schema::hasColumn('clients', 'company_number')) {
                $table->string('company_number')->nullable();
            }

            if (! Schema::hasColumn('clients', 'source')) {
                $table->string('source')->default('companies_house');
            }

            if (! Schema::hasColumn('clients', 'raw_json')) {
                $table->text('raw_json')->nullable();
            }
        });
    }

    public function down(): void
    {
        // No-op for safety (SQLite doesn’t like dropColumn in some cases).
        // If you need to roll back columns, create a dedicated migration.
    }
}

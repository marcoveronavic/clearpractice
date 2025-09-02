<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Create or upgrade `clients` table
        if (! Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->id();
                $table->string('name');                          // e.g. director name
                $table->string('company_number')->nullable();
                $table->string('company_name')->nullable();
                $table->string('source')->default('companies_house');
                $table->json('raw_json')->nullable();            // JSON payload (TEXT on SQLite)
                $table->timestamps();
                $table->index('company_number');
            });
        } else {
            Schema::table('clients', function (Blueprint $table) {
                if (! Schema::hasColumn('clients', 'name'))            $table->string('name')->nullable();
                if (! Schema::hasColumn('clients', 'company_number'))  $table->string('company_number')->nullable()->index();
                if (! Schema::hasColumn('clients', 'company_name'))    $table->string('company_name')->nullable();
                if (! Schema::hasColumn('clients', 'source'))          $table->string('source')->nullable();
                if (! Schema::hasColumn('clients', 'raw_json'))        $table->json('raw_json')->nullable();
                if (! Schema::hasColumn('clients', 'created_at'))      $table->timestamps(); // adds both created_at & updated_at
            });

            // Backfill a default source if null (SQLite-safe)
            try { DB::table('clients')->whereNull('source')->update(['source' => 'companies_house']); } catch (\Throwable $e) {}
        }

        // Create pivot if missing
        if (! Schema::hasTable('client_user')) {
            Schema::create('client_user', function (Blueprint $table) {
                $table->unsignedBigInteger('client_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->primary(['client_id', 'user_id']);
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // Keep clients; only remove the pivot on rollback
        if (Schema::hasTable('client_user')) {
            Schema::drop('client_user');
        }
    }
};

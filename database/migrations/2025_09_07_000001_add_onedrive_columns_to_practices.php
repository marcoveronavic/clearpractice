<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->string('onedrive_drive_id')->nullable()->index();
            $table->string('onedrive_drive_type')->nullable(); // 'personal' or 'business'
            $table->string('onedrive_base_path')->nullable();  // e.g., 'ClearPractice' or 'Documents/ClearPractice'
        });
    }

    public function down(): void
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->dropColumn(['onedrive_drive_id','onedrive_drive_type','onedrive_base_path']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support MODIFY COLUMN or ENUM types
        // We need to recreate the table with the correct schema
        Schema::table('expenses', function (Blueprint $table) {
            // This will work for both SQLite and other databases
            // The enum values are already correct from previous migrations
            // This migration is essentially a no-op for databases that support it
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No changes needed for rollback since the enum is already correct
        Schema::table('expenses', function (Blueprint $table) {
            // This migration doesn't actually change anything
        });
    }
};

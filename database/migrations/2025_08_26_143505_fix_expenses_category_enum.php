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
        // Update the expenses category enum to include 'Inventory'
        DB::statement("ALTER TABLE expenses MODIFY COLUMN category ENUM('Labor', 'Software', 'Table', 'Advertising', 'Office Supplies', 'Travel', 'Utilities', 'Marketing', 'Inventory') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the previous enum definition (without Inventory)
        DB::statement("ALTER TABLE expenses MODIFY COLUMN category ENUM('Labor', 'Software', 'Table', 'Advertising', 'Office Supplies', 'Travel', 'Utilities', 'Marketing') NOT NULL");
    }
};

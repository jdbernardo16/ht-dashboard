<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Rename date to expense_date to match controller
            $table->renameColumn('date', 'expense_date');

            // Add missing fields to align with sales module pattern
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'online', 'bank_transfer'])->default('cash');
            $table->string('merchant')->nullable();
            $table->string('receipt_number')->nullable();
            $table->decimal('tax_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();

            // Update category enum to include more options
            $table->enum('category', [
                'Labor',
                'Software',
                'Table',
                'Advertising',
                'Office Supplies',
                'Travel',
                'Utilities',
                'Marketing',
                'Inventory'
            ])->change();

            // Add additional indexes for new fields
            $table->index('status');
            $table->index('payment_method');
            $table->index(['user_id', 'expense_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('expense_date', 'date');
            $table->dropColumn(['status', 'payment_method', 'merchant', 'receipt_number', 'tax_amount', 'notes']);
            $table->enum('category', ['Labor', 'Software', 'Table', 'Advertising', 'Inventory'])->change();
        });
    }
};

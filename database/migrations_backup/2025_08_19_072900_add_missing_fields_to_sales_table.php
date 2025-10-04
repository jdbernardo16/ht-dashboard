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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('product_name')->after('type');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->after('description');
            $table->enum('payment_method', ['cash', 'card', 'online'])->default('cash')->after('status');
            $table->renameColumn('date', 'sale_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'status', 'payment_method']);
            $table->renameColumn('sale_date', 'date');
        });
    }
};

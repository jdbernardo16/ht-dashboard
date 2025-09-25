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
        Schema::table('content_posts', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['client_id']);

            // Add the new foreign key constraint that references the clients table
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_posts', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['client_id']);

            // Revert back to the old foreign key constraint that references the users table
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};

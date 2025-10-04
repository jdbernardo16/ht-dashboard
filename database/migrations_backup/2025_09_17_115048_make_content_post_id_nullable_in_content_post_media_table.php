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
        Schema::table('content_post_media', function (Blueprint $table) {
            // Make the content_post_id column nullable
            $table->unsignedBigInteger('content_post_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_post_media', function (Blueprint $table) {
            // Revert to not nullable
            $table->unsignedBigInteger('content_post_id')->nullable(false)->change();
        });
    }
};

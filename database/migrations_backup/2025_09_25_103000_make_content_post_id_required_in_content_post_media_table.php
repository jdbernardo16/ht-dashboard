<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, let's check if there are any orphaned records (content_post_id is null)
        // and handle them appropriately
        $orphanedRecords = DB::table('content_post_media')->whereNull('content_post_id')->count();

        if ($orphanedRecords > 0) {
            // Log the orphaned records for debugging
            Log::warning("Found {$orphanedRecords} orphaned content_post_media records. These will be deleted.");

            // Delete orphaned records since they can't be associated with a content post
            DB::table('content_post_media')->whereNull('content_post_id')->delete();
        }

        // Now make the content_post_id column not nullable
        Schema::table('content_post_media', function (Blueprint $table) {
            $table->unsignedBigInteger('content_post_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to nullable
        Schema::table('content_post_media', function (Blueprint $table) {
            $table->unsignedBigInteger('content_post_id')->nullable()->change();
        });
    }
};

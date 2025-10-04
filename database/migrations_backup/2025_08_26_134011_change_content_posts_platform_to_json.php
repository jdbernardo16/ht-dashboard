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
            $table->json('platforms')->nullable()->after('platform');
        });

        // Copy existing platform data to new platforms field
        \DB::statement("UPDATE content_posts SET platforms = JSON_ARRAY(platform) WHERE platform IS NOT NULL");

        Schema::table('content_posts', function (Blueprint $table) {
            // Drop the index first before dropping the column
            $table->dropIndex(['platform']);
            $table->dropColumn('platform');
            $table->renameColumn('platforms', 'platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_posts', function (Blueprint $table) {
            $table->enum('platform', [
                'website',
                'facebook',
                'instagram',
                'twitter',
                'linkedin',
                'tiktok',
                'youtube',
                'pinterest',
                'email',
                'other',
            ])->nullable()->after('platforms');
        });

        // Convert JSON array back to single platform
        \DB::statement("UPDATE content_posts SET platform = JSON_UNQUOTE(JSON_EXTRACT(platform, '$[0]')) WHERE platform IS NOT NULL");

        Schema::table('content_posts', function (Blueprint $table) {
            $table->dropColumn('platforms');
            // Recreate the index on the platform column
            $table->index('platform');
        });
    }
};

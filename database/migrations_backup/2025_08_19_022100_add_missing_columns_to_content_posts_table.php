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
            $table->foreignId('client_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('title')->after('client_id');
            $table->string('content_type')->after('platform');
            $table->text('description')->nullable()->after('title');
            $table->string('content_url')->nullable()->after('description');
            $table->date('scheduled_date')->nullable()->after('date');
            $table->date('published_date')->nullable()->after('scheduled_date');
            $table->string('status')->default('draft')->after('published_date');
            $table->string('content_category')->nullable()->after('status');
            $table->json('tags')->nullable()->after('content_category');
            $table->text('notes')->nullable()->after('tags');

            // Update platform enum to include all platforms
            $table->enum('platform', [
                'facebook',
                'instagram',
                'twitter',
                'linkedin',
                'tiktok',
                'youtube',
                'pinterest',
                'other'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_posts', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn([
                'client_id',
                'title',
                'content_type',
                'description',
                'content_url',
                'scheduled_date',
                'published_date',
                'status',
                'content_category',
                'tags',
                'notes'
            ]);

            // Revert platform enum to original
            $table->enum('platform', ['Facebook', 'TikTok', 'YouTube'])->change();
        });
    }
};

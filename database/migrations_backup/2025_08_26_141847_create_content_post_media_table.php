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
        Schema::create('content_post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_post_id')->constrained('content_posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->string('original_name');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Indexes for performance
            $table->index('content_post_id');
            $table->index('user_id');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_post_media');
    }
};

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
        // Users table with profile fields
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('role')->default('VA');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Password reset tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Cache tables
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // Jobs tables
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Personal access tokens for Sanctum
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        // Clients table
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('company')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['first_name', 'last_name']);
            $table->index('email');
            $table->index('company');
        });

        // Categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Goals table with all fields
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('target_value', 10, 2);
            $table->decimal('current_value', 10, 2)->default(0.00);
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('labor_hours', 8, 2)->nullable();
            $table->enum('type', ['sales', 'revenue', 'expense', 'task', 'content', 'other'])->default('other');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'failed'])->default('not_started');
            $table->decimal('progress', 8, 2)->default(0);
            $table->string('quarter');
            $table->integer('year');
            $table->date('deadline');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('quarter');
            $table->index('year');
            $table->index(['user_id', 'quarter', 'year']);
        });

        // Tasks table with all fields
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'not_started'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('category')->nullable();
            $table->decimal('estimated_hours', 5, 2)->nullable();
            $table->decimal('actual_hours', 5, 2)->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->onDelete('cascade');
            $table->foreignId('related_goal_id')->nullable()->constrained('goals')->onDelete('cascade');
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_frequency', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('status');
            $table->index('priority');
            $table->index('assigned_to');
            $table->index('due_date');
        });

        // Daily summaries table
        Schema::create('daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date')->unique();
            $table->integer('total_tasks')->default(0);
            $table->integer('completed_tasks')->default(0);
            $table->decimal('productivity_score', 5, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'date']);
            $table->unique(['user_id', 'date']);
        });

        // Sales table with all fields
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->enum('type', ['Cards', 'Listings', 'VA', 'Consigned']);
            $table->string('product_name');
            $table->decimal('amount', 10, 2);
            $table->date('sale_date');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'online'])->default('cash');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('client_id');
            $table->index('type');
            $table->index('sale_date');
            $table->index(['user_id', 'sale_date']);
        });

        // Content posts table with all fields
        Schema::create('content_posts', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade');
            $table->json('platform')->nullable();
            $table->string('content_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('content_url')->nullable();
            $table->integer('post_count')->default(0);
            $table->date('date')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->date('published_date')->nullable();
            $table->string('status')->default('draft');
            $table->string('content_category')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->json('engagement_metrics')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('client_id');
            $table->index('date');
            $table->index(['user_id', 'date']);
        });

        // Expenses table with all fields
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->enum('category', ['Labor', 'Software', 'Table', 'Advertising', 'Office Supplies', 'Travel', 'Utilities', 'Marketing', 'Inventory']);
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'online', 'bank_transfer'])->default('cash');
            $table->string('merchant')->nullable();
            $table->string('receipt_number')->nullable();
            $table->decimal('tax_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('category');
            $table->index('expense_date');
            $table->index('status');
            $table->index('payment_method');
            $table->index(['user_id', 'expense_date']);
        });

        // Task media table
        Schema::create('task_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
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
            $table->index('task_id');
            $table->index('user_id');
            $table->index('is_primary');
        });

        // Notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // e.g., system_alert, task_update, expense_approved, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data for the notification
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('type');
            $table->index('read_at');
            $table->index(['user_id', 'read_at']);
        });

        // Content post media table
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
        // Drop all tables in reverse order to handle foreign key constraints
        Schema::dropIfExists('content_post_media');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('task_media');
        Schema::dropIfExists('goals');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('content_posts');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('daily_summaries');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
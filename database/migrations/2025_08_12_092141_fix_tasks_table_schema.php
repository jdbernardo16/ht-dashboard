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
        Schema::table('tasks', function (Blueprint $table) {
            // Add user_id column if it doesn't exist
            if (!Schema::hasColumn('tasks', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
            }

            // Make assigned_to nullable
            $table->foreignId('assigned_to')->nullable()->change();

            // Add missing columns from validation rules
            $table->string('category')->nullable()->after('assigned_to');
            $table->decimal('estimated_hours', 5, 2)->nullable()->after('category');
            $table->decimal('actual_hours', 5, 2)->nullable()->after('estimated_hours');
            $table->json('tags')->nullable()->after('actual_hours');
            $table->text('notes')->nullable()->after('tags');
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->onDelete('cascade')->after('notes');
            $table->foreignId('related_goal_id')->nullable()->constrained('goals')->onDelete('cascade')->after('parent_task_id');
            $table->boolean('is_recurring')->default(false)->after('related_goal_id');
            $table->enum('recurring_frequency', ['daily', 'weekly', 'monthly', 'yearly'])->nullable()->after('is_recurring');

            // Update status enum to include 'cancelled'
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropForeign(['assigned_to']);
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade')->change();
            $table->dropColumn(['category', 'estimated_hours', 'actual_hours', 'tags', 'notes', 'parent_task_id', 'related_goal_id', 'is_recurring', 'recurring_frequency']);
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending')->change();
        });
    }
};

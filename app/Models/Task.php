<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'user_id',
        'assigned_to',
        'due_date',
        'completed_at',
        'category',
        'estimated_hours',
        'actual_hours',
        'tags',
        'notes',
        'parent_task_id',
        'related_goal_id',
        'is_recurring',
        'recurring_frequency',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'tags' => 'array',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all media files associated with this task
     */
    public function media()
    {
        return $this->hasMany(TaskMedia::class);
    }

    /**
     * Get the primary media file for this task
     */
    public function primaryMedia()
    {
        return $this->hasOne(TaskMedia::class)->where('is_primary', true);
    }
    /**
     * Get the related goal for this task
     */
    public function goal()
    {
        return $this->belongsTo(Goal::class, 'related_goal_id');
    }
}

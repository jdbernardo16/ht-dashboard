<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'total_tasks',
        'completed_tasks',
        'productivity_score',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'productivity_score' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'post_count',
        'date',
        'engagement_metrics',
    ];

    protected $casts = [
        'date' => 'date',
        'engagement_metrics' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

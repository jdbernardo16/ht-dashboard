<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ImageService;
use Illuminate\Support\Facades\Log;

class ContentPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'category_id',
        'platform',
        'content_type',
        'title',
        'description',
        'content_url',
        'image',
        'post_count',
        'scheduled_date',
        'published_date',
        'status',
        'engagement_metrics',
        'content_category',
        'tags',
        'notes',
        'meta_description',
        'seo_keywords',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'published_date' => 'date',
        'post_count' => 'integer',
        'engagement_metrics' => 'array',
        'platform' => 'array',
        'tags' => 'array',
        'status' => 'string',
        'content_type' => 'string',
        'content_category' => 'string',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Clean up images when content post is being deleted
        static::deleting(function ($contentPost) {
            try {
                $imageService = app(ImageService::class);

                // Delete main image
                if ($contentPost->image) {
                    $imageService->deleteImage($contentPost->image);
                    Log::info('ContentPost main image cleaned up during model deletion', [
                        'content_post_id' => $contentPost->id,
                        'image_path' => $contentPost->image
                    ]);
                }

                // Delete associated media files
                $mediaFiles = $contentPost->media->pluck('file_path')->toArray();
                if (!empty($mediaFiles)) {
                    $deleteResults = $imageService->deleteImages($mediaFiles);
                    Log::info('ContentPost media files cleaned up during model deletion', [
                        'content_post_id' => $contentPost->id,
                        'files' => $mediaFiles,
                        'results' => $deleteResults
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to clean up images during ContentPost deletion', [
                    'content_post_id' => $contentPost->id,
                    'error' => $e->getMessage()
                ]);

                // Don't prevent deletion, just log the error
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all media files associated with this content post
     */
    public function media()
    {
        return $this->hasMany(ContentPostMedia::class);
    }

    /**
     * Get the primary media file for this content post
     */
    public function primaryMedia()
    {
        return $this->hasOne(ContentPostMedia::class)->where('is_primary', true);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by platform
     */
    public function scopePlatform($query, $platform)
    {
        return $query->whereJsonContains('platform', $platform);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('scheduled_date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by content type
     */
    public function scopeContentType($query, $type)
    {
        return $query->where('content_type', $type);
    }

    /**
     * Check if post is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_date !== null;
    }

    /**
     * Check if post is scheduled
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_date > now();
    }

    /**
     * Check if post is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Get engagement rate
     */
    public function getEngagementRateAttribute()
    {
        if (!$this->engagement_metrics || !isset($this->engagement_metrics['views'])) {
            return 0;
        }

        $views = $this->engagement_metrics['views'] ?? 0;
        $likes = $this->engagement_metrics['likes'] ?? 0;
        $comments = $this->engagement_metrics['comments'] ?? 0;
        $shares = $this->engagement_metrics['shares'] ?? 0;

        if ($views === 0) {
            return 0;
        }

        return round((($likes + $comments + $shares) / $views) * 100, 2);
    }

    /**
     * Get total engagement count
     */
    public function getTotalEngagementAttribute()
    {
        if (!$this->engagement_metrics) {
            return 0;
        }

        $likes = $this->engagement_metrics['likes'] ?? 0;
        $comments = $this->engagement_metrics['comments'] ?? 0;
        $shares = $this->engagement_metrics['shares'] ?? 0;

        return $likes + $comments + $shares;
    }
}

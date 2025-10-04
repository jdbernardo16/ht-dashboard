<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ImageService;
use Illuminate\Support\Facades\Log;

class ContentPostMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_post_id',
        'user_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'original_name',
        'description',
        'order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['url'];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Clean up file when media record is being deleted
        static::deleting(function ($media) {
            try {
                $imageService = app(ImageService::class);

                if ($media->file_path) {
                    $imageService->deleteImage($media->file_path);
                    Log::info('ContentPostMedia file cleaned up during model deletion', [
                        'media_id' => $media->id,
                        'file_path' => $media->file_path
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Failed to clean up file during ContentPostMedia deletion', [
                    'media_id' => $media->id,
                    'file_path' => $media->file_path,
                    'error' => $e->getMessage()
                ]);

                // Don't prevent deletion, just log the error
            }
        });
    }

    public function contentPost()
    {
        return $this->belongsTo(ContentPost::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the URL for the media file
     */
    public function getUrlAttribute()
    {
        if (!$this->file_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->file_path);
    }

    /**
     * Get the human-readable file size
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'company',
        'category',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['profile_image_url'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the sales for the client.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the content posts for the client.
     */
    public function contentPosts(): HasMany
    {
        return $this->hasMany(ContentPost::class);
    }

    /**
     * Get all media files associated with this client.
     */
    public function media(): HasMany
    {
        return $this->hasMany(ClientMedia::class);
    }

    /**
     * Get the primary media file for this client.
     */
    public function primaryMedia()
    {
        return $this->hasOne(ClientMedia::class)->where('is_primary', true);
    }

    /**
     * Get the profile image URL for the client.
     */
    public function getProfileImageUrlAttribute()
    {
        $primaryMedia = $this->primaryMedia;
        return $primaryMedia ? $primaryMedia->url : null;
    }

    /**
     * Get the client's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope a query to search clients by name, email, or company.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('company', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
        });
    }

    /**
     * Scope a query to filter clients by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter clients by company.
     */
    public function scopeCompany($query, string $company)
    {
        return $query->where('company', 'like', "%{$company}%");
    }

    /**
     * Scope a query to apply multiple filters.
     */
    public function scopeFilter($query, array $filters = [])
    {
        // Apply text search if provided
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply category filter if provided
        if (!empty($filters['category'])) {
            $query->category($filters['category']);
        }

        // Apply company filter if provided
        if (!empty($filters['company'])) {
            $query->company($filters['company']);
        }

        return $query;
    }
}

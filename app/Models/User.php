<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'avatar_url',
        'email_preferences',
        'email_notifications_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'email_preferences' => 'array',
            'email_notifications_enabled' => 'boolean',
        ];
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function dailySummaries()
    {
        return $this->hasMany(DailySummary::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function contentPosts()
    {
        return $this->hasMany(ContentPost::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Get the user's notifications.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has manager role
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user has virtual assistant role
     */
    public function isVA(): bool
    {
        return $this->role === 'va';
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        return in_array($this->role, $roles);
    }

    /**
     * Get the user's email preferences with defaults
     */
    public function getEmailPreferencesAttribute($value): array
    {
        $defaults = [
            'task_assignment' => true,
            'task_completion' => true,
            'new_sale' => true,
            'expense_approval' => true,
            'goal_progress' => true,
            'content_published' => true,
        ];

        $decodedValue = is_string($value) ? json_decode($value, true) : $value;
        
        return array_merge($defaults, $decodedValue ?? []);
    }

    /**
     * Check if user should receive email notifications for a specific type
     */
    public function shouldReceiveEmailNotification(string $type): bool
    {
        // Check if email notifications are globally enabled
        if (!$this->email_notifications_enabled) {
            return false;
        }

        // Check if user has verified email (if email verification is enabled)
        if (config('auth.verification.enabled') && !$this->hasVerifiedEmail()) {
            return false;
        }

        // Check specific notification type preference
        $preferences = $this->email_preferences ?? [];
        
        return ($preferences[$type] ?? true) === true;
    }

    /**
     * Update email preferences
     */
    public function updateEmailPreferences(array $preferences): bool
    {
        try {
            $this->email_preferences = array_merge(
                $this->email_preferences,
                $preferences
            );
            return $this->save();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Toggle email notifications
     */
    public function toggleEmailNotifications(bool $enabled): bool
    {
        try {
            $this->email_notifications_enabled = $enabled;
            return $this->save();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get default email preferences for new users
     */
    public static function getDefaultEmailPreferences(): array
    {
        return [
            'task_assignment' => true,
            'task_completion' => true,
            'new_sale' => true,
            'expense_approval' => true,
            'goal_progress' => true,
            'content_published' => true,
        ];
    }

    /**
     * Initialize email preferences for new users
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (is_null($user->email_notifications_enabled)) {
                $user->email_notifications_enabled = true;
            }
            
            if (is_null($user->email_preferences)) {
                $user->email_preferences = self::getDefaultEmailPreferences();
            }
        });
    }
}

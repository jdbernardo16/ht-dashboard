<?php

namespace App\Observers;

use App\Models\ContentPost;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class ContentPostObserver
{
    /**
     * Handle the ContentPost "created" event.
     */
    public function created(ContentPost $contentPost): void
    {
        try {
            // Notify admins and managers about new content posts
            $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
            
            foreach ($managersAndAdmins as $user) {
                // Don't notify the creator
                if ($user->id !== $contentPost->user_id) {
                    $platforms = is_array($contentPost->platform) ? implode(', ', $contentPost->platform) : $contentPost->platform;
                    
                    NotificationService::createNotification(
                        $user,
                        'content_created',
                        'New Content Created',
                        "New content '{$contentPost->title}' created by {$contentPost->user->name} for {$platforms}",
                        [
                            'content_post_id' => $contentPost->id,
                            'content_title' => $contentPost->title,
                            'content_type' => $contentPost->content_type,
                            'platforms' => $contentPost->platform,
                            'scheduled_date' => $contentPost->scheduled_date?->format('Y-m-d'),
                            'status' => $contentPost->status,
                            'created_by' => $contentPost->user->name,
                            'client_name' => $contentPost->client?->name,
                            'category' => $contentPost->category?->name,
                        ]
                    );
                }
            }

            // Notify the content creator
            $platforms = is_array($contentPost->platform) ? implode(', ', $contentPost->platform) : $contentPost->platform;
            NotificationService::createNotification(
                $contentPost->user,
                'content_post_created',
                'Content Post Created',
                "Your content '{$contentPost->title}' has been created for {$platforms}",
                [
                    'content_post_id' => $contentPost->id,
                    'content_title' => $contentPost->title,
                    'content_type' => $contentPost->content_type,
                    'platforms' => $contentPost->platform,
                    'scheduled_date' => $contentPost->scheduled_date?->format('Y-m-d'),
                    'status' => $contentPost->status,
                ]
            );

            // Notify content team members if it's a team-related content
            $contentTeamMembers = User::where('role', 'va')->get();
            foreach ($contentTeamMembers as $member) {
                // Don't notify the creator
                if ($member->id !== $contentPost->user_id) {
                    NotificationService::createNotification(
                        $member,
                        'team_content_created',
                        'New Team Content',
                        "New content '{$contentPost->title}' created for {$platforms}",
                        [
                            'content_post_id' => $contentPost->id,
                            'content_title' => $contentPost->title,
                            'content_type' => $contentPost->content_type,
                            'platforms' => $contentPost->platform,
                            'created_by' => $contentPost->user->name,
                            'scheduled_date' => $contentPost->scheduled_date?->format('Y-m-d'),
                        ]
                    );
                }
            }

            // If content is scheduled for today, notify about imminent publication
            if ($contentPost->scheduled_date && $contentPost->scheduled_date->isToday()) {
                $this->notifyImminentPublication($contentPost);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send content post creation notification', [
                'content_post_id' => $contentPost->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the ContentPost "updated" event.
     */
    public function updated(ContentPost $contentPost): void
    {
        try {
            $changes = $contentPost->getChanges();
            
            // Check if content status was updated
            if (isset($changes['status'])) {
                $oldStatus = $contentPost->getOriginal('status');
                $newStatus = $contentPost->status;
                $platforms = is_array($contentPost->platform) ? implode(', ', $contentPost->platform) : $contentPost->platform;

                // Notify the content creator about status change
                NotificationService::createNotification(
                    $contentPost->user,
                    'content_status_updated',
                    'Content Status Updated',
                    "Your content '{$contentPost->title}' status has been updated to {$newStatus}",
                    [
                        'content_post_id' => $contentPost->id,
                        'content_title' => $contentPost->title,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'platforms' => $contentPost->platform,
                        'updated_by' => auth()->user()->name,
                    ]
                );

                // If content was published, send publication notification
                if ($newStatus === 'published' && $contentPost->published_date) {
                    $this->notifyContentPublished($contentPost);
                }

                // If content was scheduled, notify about scheduling
                if ($newStatus === 'scheduled' && $contentPost->scheduled_date) {
                    $this->notifyContentScheduled($contentPost);
                }

                // Notify admins/managers about important status changes
                if (in_array($newStatus, ['published', 'failed', 'cancelled'])) {
                    $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
                    foreach ($managersAndAdmins as $user) {
                        // Don't notify the person who changed it
                        if ($user->id !== auth()->id()) {
                            NotificationService::createNotification(
                                $user,
                                'content_status_updated',
                                'Content Status Updated',
                                "Content '{$contentPost->title}' status changed to {$newStatus}",
                                [
                                    'content_post_id' => $contentPost->id,
                                    'content_title' => $contentPost->title,
                                    'old_status' => $oldStatus,
                                    'new_status' => $newStatus,
                                    'platforms' => $contentPost->platform,
                                    'content_creator' => $contentPost->user->name,
                                    'updated_by' => auth()->user()->name,
                                ]
                            );
                        }
                    }
                }
            }

            // Check if scheduled date was updated
            if (isset($changes['scheduled_date'])) {
                $this->notifyScheduleChanged($contentPost);
            }

            // Check if published date was updated
            if (isset($changes['published_date'])) {
                $this->notifyPublicationDateChanged($contentPost);
            }

            // Check if engagement metrics were updated (for published content)
            if (isset($changes['engagement_metrics']) && $contentPost->status === 'published') {
                $this->notifyEngagementUpdate($contentPost);
            }

            // Check if content is scheduled for tomorrow (reminder)
            if (isset($changes['scheduled_date']) || isset($changes['status'])) {
                if ($contentPost->scheduled_date && $contentPost->status === 'scheduled') {
                    $daysUntilPublication = now()->diffInDays($contentPost->scheduled_date, false);
                    
                    // Send reminder 1 day before publication
                    if ($daysUntilPublication === 1) {
                        $this->notifyPublicationReminder($contentPost);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send content post update notification', [
                'content_post_id' => $contentPost->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify about content publication.
     */
    private function notifyContentPublished(ContentPost $contentPost): void
    {
        $platforms = is_array($contentPost->platform) ? implode(', ', $contentPost->platform) : $contentPost->platform;
        
        // Notify the content creator
        NotificationService::sendContentPublication(
            $contentPost->user,
            $contentPost->title,
            $platforms,
            [
                'content_post' => $contentPost,
                'content_post_id' => $contentPost->id,
                'content_type' => $contentPost->content_type,
                'published_date' => $contentPost->published_date->format('Y-m-d H:i:s'),
                'content_url' => $contentPost->content_url,
            ]
        );

        // Notify admins and managers
        $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
        foreach ($managersAndAdmins as $user) {
            // Don't notify the creator
            if ($user->id !== $contentPost->user_id) {
                NotificationService::createNotification(
                    $user,
                    'content_published',
                    'Content Published',
                    "Content '{$contentPost->title}' has been published on {$platforms}",
                    [
                        'content_post_id' => $contentPost->id,
                        'content_title' => $contentPost->title,
                        'platforms' => $contentPost->platform,
                        'published_by' => $contentPost->user->name,
                        'published_date' => $contentPost->published_date->format('Y-m-d H:i:s'),
                        'content_url' => $contentPost->content_url,
                    ]
                );
            }
        }

        // Notify content team members
        $contentTeamMembers = User::where('role', 'va')->get();
        foreach ($contentTeamMembers as $member) {
            // Don't notify the creator
            if ($member->id !== $contentPost->user_id) {
                NotificationService::createNotification(
                    $member,
                    'team_content_published',
                    'Team Content Published',
                    "Team content '{$contentPost->title}' has been published on {$platforms}",
                    [
                        'content_post_id' => $contentPost->id,
                        'content_title' => $contentPost->title,
                        'platforms' => $contentPost->platform,
                        'published_by' => $contentPost->user->name,
                    ]
                );
            }
        }
    }

    /**
     * Notify about content scheduling.
     */
    private function notifyContentScheduled(ContentPost $contentPost): void
    {
        $platforms = is_array($contentPost->platform) ? implode(', ', $contentPost->platform) : $contentPost->platform;
        
        // Notify admins and managers
        $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
        foreach ($managersAndAdmins as $user) {
            // Don't notify the creator
            if ($user->id !== $contentPost->user_id) {
                NotificationService::createNotification(
                    $user,
                    'content_scheduled',
                    'Content Scheduled',
                    "Content '{$contentPost->title}' scheduled for {$contentPost->scheduled_date->format('Y-m-d')} on {$platforms}",
                    [
                        'content_post_id' => $contentPost->id,
                        'content_title' => $contentPost->title,
                        'platforms' => $contentPost->platform,
                        'scheduled_date' => $contentPost->scheduled_date->format('Y-m-d'),
                        'scheduled_by' => $contentPost->user->name,
                    ]
                );
            }
        }
    }

    /**
     * Notify about imminent publication.
     */
    private function notifyImminentPublication(ContentPost $contentPost): void
    {
        $platforms = is_array($contentPost->platform) ? implode(', ', $contentPost->platform) : $contentPost->platform;
        
        // Notify the content creator
        NotificationService::sendReminder(
            $contentPost->user,
            'Content Publication Today',
            "Your content '{$contentPost->title}' is scheduled for publication today on {$platforms}",
            [
                'content_post_id' => $contentPost->id,
                'content_title' => $contentPost->title,
                'platforms' => $contentPost->platform,
                'scheduled_date' => $contentPost->scheduled_date->format('Y-m-d'),
            ]
        );
    }

    /**
     * Notify about schedule change.
     */
    private function notifyScheduleChanged(ContentPost $contentPost): void
    {
        $platforms = is_array($contentPost->platform) ? implode(', ', $contentPost->platform) : $contentPost->platform;
        $oldDate = $contentPost->getOriginal('scheduled_date');
        
        // Notify the content creator
        NotificationService::createNotification(
            $contentPost->user,
            'content_schedule_changed',
            'Content Schedule Changed',
            "Schedule for '{$contentPost->title}' has been updated",
            [
                'content_post_id' => $contentPost->id,
                'content_title' => $contentPost->title,
                'old_scheduled_date' => $oldDate,
                'new_scheduled_date' => $contentPost->scheduled_date?->format('Y-m-d'),
                'platforms' => $contentPost->platform,
                'updated_by' => auth()->user()->name,
            ]
        );
    }

    /**
     * Notify about publication date change.
     */
    private function notifyPublicationDateChanged(ContentPost $contentPost): void
    {
        $platforms = is_array($contentPost->platform) ? implode(', ', $contentPost->platform) : $contentPost->platform;
        
        // Notify admins and managers
        $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
        foreach ($managersAndAdmins as $user) {
            NotificationService::createNotification(
                $user,
                'content_publication_date_changed',
                'Content Publication Date Updated',
                "Publication date for '{$contentPost->title}' has been updated",
                [
                    'content_post_id' => $contentPost->id,
                    'content_title' => $contentPost->title,
                    'platforms' => $contentPost->platform,
                    'updated_by' => auth()->user()->name,
                ]
            );
        }
    }

    /**
     * Notify about engagement update.
     */
    private function notifyEngagementUpdate(ContentPost $contentPost): void
    {
        $totalEngagement = $contentPost->total_engagement;
        
        // Notify if content has high engagement (threshold: 100 total engagements)
        if ($totalEngagement >= 100) {
            $platforms = is_array($contentPost->platform) ? implode(', ', $contentPost->platform) : $contentPost->platform;
            
            // Notify admins and managers
            $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
            foreach ($managersAndAdmins as $user) {
                NotificationService::createNotification(
                    $user,
                    'content_high_engagement',
                    'Content High Engagement',
                    "Content '{$contentPost->title}' has {$totalEngagement} total engagements on {$platforms}",
                    [
                        'content_post_id' => $contentPost->id,
                        'content_title' => $contentPost->title,
                        'platforms' => $contentPost->platform,
                        'total_engagement' => $totalEngagement,
                        'engagement_rate' => $contentPost->engagement_rate,
                        'content_creator' => $contentPost->user->name,
                    ]
                );
            }

            // Notify the content creator
            NotificationService::createNotification(
                $contentPost->user,
                'content_high_engagement',
                'Your Content is Performing Well!',
                "Your content '{$contentPost->title}' has {$totalEngagement} total engagements!",
                [
                    'content_post_id' => $contentPost->id,
                    'content_title' => $contentPost->title,
                    'platforms' => $contentPost->platform,
                    'total_engagement' => $totalEngagement,
                    'engagement_rate' => $contentPost->engagement_rate,
                ]
            );
        }
    }

    /**
     * Notify about publication reminder.
     */
    private function notifyPublicationReminder(ContentPost $contentPost): void
    {
        $platforms = is_array($contentPost->platform) ? implode(', ', $contentPost->platform) : $contentPost->platform;
        
        // Notify the content creator
        NotificationService::sendReminder(
            $contentPost->user,
            'Content Publication Tomorrow',
            "Your content '{$contentPost->title}' is scheduled for publication tomorrow on {$platforms}",
            [
                'content_post_id' => $contentPost->id,
                'content_title' => $contentPost->title,
                'platforms' => $contentPost->platform,
                'scheduled_date' => $contentPost->scheduled_date->format('Y-m-d'),
            ]
        );
    }
}
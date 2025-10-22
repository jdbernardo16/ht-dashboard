<?php

namespace App\Observers;

use App\Models\Goal;
use App\Models\User;
use App\Services\NotificationService;
use App\Traits\AdministrativeAlertsTrait;
use Illuminate\Support\Facades\Log;

class GoalObserver
{
    use AdministrativeAlertsTrait;
    /**
     * Handle the Goal "created" event.
     */
    public function created(Goal $goal): void
    {
        try {
            // Notify admins and managers about new goals
            $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
            
            foreach ($managersAndAdmins as $user) {
                // Don't notify the creator
                if ($user->id !== $goal->user_id) {
                    NotificationService::createNotification(
                        $user,
                        'goal_created',
                        'New Goal Set',
                        "New goal '{$goal->title}' set by {$goal->user->name} with target {$goal->target_value}",
                        [
                            'goal_id' => $goal->id,
                            'goal_title' => $goal->title,
                            'goal_type' => $goal->type,
                            'target_value' => $goal->target_value,
                            'deadline' => $goal->deadline?->format('Y-m-d'),
                            'priority' => $goal->priority,
                            'created_by' => $goal->user->name,
                            'quarter' => $goal->quarter,
                            'year' => $goal->year,
                        ]
                    );
                }
            }

            // Notify the goal creator that it's been set
            NotificationService::createNotification(
                $goal->user,
                'goal_set',
                'Goal Set Successfully',
                "Your goal '{$goal->title}' has been set with target {$goal->target_value}",
                [
                    'goal_id' => $goal->id,
                    'goal_title' => $goal->title,
                    'goal_type' => $goal->type,
                    'target_value' => $goal->target_value,
                    'deadline' => $goal->deadline?->format('Y-m-d'),
                    'priority' => $goal->priority,
                    'quarter' => $goal->quarter,
                    'year' => $goal->year,
                ]
            );

            // Notify team members if it's a team-related goal
            if (in_array($goal->type, ['sales', 'revenue', 'task'])) {
                $teamMembers = User::where('role', 'va')->get();
                foreach ($teamMembers as $member) {
                    // Don't notify the creator
                    if ($member->id !== $goal->user_id) {
                        NotificationService::createNotification(
                            $member,
                            'team_goal_set',
                            'New Team Goal',
                            "New team goal '{$goal->title}' set for {$goal->type}",
                            [
                                'goal_id' => $goal->id,
                                'goal_title' => $goal->title,
                                'goal_type' => $goal->type,
                                'target_value' => $goal->target_value,
                                'deadline' => $goal->deadline?->format('Y-m-d'),
                                'set_by' => $goal->user->name,
                            ]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send goal creation notification', [
                'goal_id' => $goal->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Goal "updated" event.
     */
    public function updated(Goal $goal): void
    {
        try {
            $changes = $goal->getChanges();
            $progressPercentage = $goal->progress_percentage;
            
            // Check for progress milestones (25%, 50%, 75%, 100%)
            if (isset($changes['current_value']) || isset($changes['target_value'])) {
                $milestones = [25, 50, 75, 100];
                
                foreach ($milestones as $milestone) {
                    if ($progressPercentage >= $milestone) {
                        // Check if we haven't already notified for this milestone
                        $milestoneKey = "goal_{$goal->id}_milestone_{$milestone}";
                        if (!cache()->has($milestoneKey)) {
                            cache()->put($milestoneKey, true, now()->addDays(30));
                            
                            if ($milestone === 100) {
                                // Goal completed!
                                $this->notifyGoalCompleted($goal);
                            } else {
                                // Milestone reached
                                $this->notifyMilestoneReached($goal, $milestone);
                            }
                            break; // Only notify for the highest milestone reached
                        }
                    }
                }
            }

            // Check if goal status changed
            if (isset($changes['status'])) {
                $oldStatus = $goal->getOriginal('status');
                $newStatus = $goal->status;

                // Notify the goal creator about status change
                NotificationService::createNotification(
                    $goal->user,
                    'goal_status_updated',
                    'Goal Status Updated',
                    "Your goal '{$goal->title}' status has been updated to {$newStatus}",
                    [
                        'goal_id' => $goal->id,
                        'goal_title' => $goal->title,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'progress' => $progressPercentage,
                        'updated_by' => auth()->user()->name,
                    ]
                );

                // Notify admins/managers about important status changes
                if (in_array($newStatus, ['completed', 'cancelled', 'on_hold'])) {
                    $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
                    foreach ($managersAndAdmins as $user) {
                        // Don't notify the person who changed it
                        if ($user->id !== auth()->id()) {
                            NotificationService::createNotification(
                                $user,
                                'goal_status_updated',
                                'Goal Status Updated',
                                "Goal '{$goal->title}' status changed to {$newStatus}",
                                [
                                    'goal_id' => $goal->id,
                                    'goal_title' => $goal->title,
                                    'old_status' => $oldStatus,
                                    'new_status' => $newStatus,
                                    'goal_owner' => $goal->user->name,
                                    'updated_by' => auth()->user()->name,
                                ]
                            );
                        }
                    }
                }
            }

            // Check if deadline is approaching
            if (isset($changes['deadline']) || isset($changes['status'])) {
                if ($goal->deadline && $goal->status !== 'completed') {
                    $daysUntilDeadline = now()->diffInDays($goal->deadline, false);
                    
                    // Notify if deadline is within 7 days or overdue
                    if ($daysUntilDeadline <= 7 && $daysUntilDeadline >= -30) {
                        $deadlineStatus = $daysUntilDeadline < 0 ? 'overdue' : 'approaching';
                        
                        // Notify the goal owner
                        NotificationService::sendReminder(
                            $goal->user,
                            'Goal Deadline',
                            "Goal '{$goal->title}' deadline is {$deadlineStatus}",
                            [
                                'goal_id' => $goal->id,
                                'goal_title' => $goal->title,
                                'deadline' => $goal->deadline->format('Y-m-d'),
                                'days_until_deadline' => $daysUntilDeadline,
                                'progress' => $progressPercentage,
                                'deadline_status' => $deadlineStatus,
                            ]
                        );

                        // Notify managers if deadline is overdue or very close
                        if ($daysUntilDeadline <= 3) {
                            $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
                            foreach ($managersAndAdmins as $user) {
                                // Don't notify the goal owner if they're also a manager
                                if ($user->id !== $goal->user_id) {
                                    NotificationService::sendReminder(
                                        $user,
                                        'Goal Deadline Alert',
                                        "Goal '{$goal->title}' deadline is {$deadlineStatus}",
                                        [
                                    'goal_id' => $goal->id,
                                    'goal_title' => $goal->title,
                                    'deadline' => $goal->deadline->format('Y-m-d'),
                                    'days_until_deadline' => $daysUntilDeadline,
                                    'progress' => $progressPercentage,
                                    'goal_owner' => $goal->user->name,
                                    'deadline_status' => $deadlineStatus,
                                ]
                            );
                        }
                            }
                        }
                    }
                    
                    // Trigger goal failure alert if deadline is overdue
                    if ($daysUntilDeadline < 0 && $progressPercentage < 100) {
                        $this->triggerGoalFailedAlert(
                            $goal,
                            "Goal deadline passed with only {$progressPercentage}% completion",
                            [
                                'days_overdue' => abs($daysUntilDeadline),
                                'progress_percentage' => $progressPercentage,
                                'target_value' => $goal->target_value,
                                'current_value' => $goal->current_value,
                                'deadline' => $goal->deadline->format('Y-m-d'),
                                'goal_owner' => $goal->user->name,
                            ]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send goal update notification', [
                'goal_id' => $goal->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify about goal completion.
     */
    private function notifyGoalCompleted(Goal $goal): void
    {
        // Notify the goal owner
        NotificationService::sendGoalAchievement(
            $goal->user,
            $goal->title,
            [
                'goal' => $goal,
                'goal_id' => $goal->id,
                'goal_type' => $goal->type,
                'target_value' => $goal->target_value,
                'current_value' => $goal->current_value,
                'completed_at' => now()->format('Y-m-d H:i:s'),
                'quarter' => $goal->quarter,
                'year' => $goal->year,
            ]
        );

        // Notify admins and managers
        $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
        foreach ($managersAndAdmins as $user) {
            NotificationService::createNotification(
                $user,
                'goal_completed',
                'Goal Completed!',
                "Goal '{$goal->title}' completed by {$goal->user->name}!",
                [
                    'goal_id' => $goal->id,
                    'goal_title' => $goal->title,
                    'goal_type' => $goal->type,
                    'target_value' => $goal->target_value,
                    'achieved_by' => $goal->user->name,
                    'completed_at' => now()->format('Y-m-d H:i:s'),
                ]
            );
        }

        // Notify team members for team goals
        if (in_array($goal->type, ['sales', 'revenue', 'task'])) {
            $teamMembers = User::where('role', 'va')->get();
            foreach ($teamMembers as $member) {
                // Don't notify the goal owner
                if ($member->id !== $goal->user_id) {
                    NotificationService::createNotification(
                        $member,
                        'team_goal_completed',
                        'Team Goal Achieved!',
                        "Team goal '{$goal->title}' has been completed!",
                        [
                            'goal_id' => $goal->id,
                            'goal_title' => $goal->title,
                            'goal_type' => $goal->type,
                            'achieved_by' => $goal->user->name,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Notify about milestone reached.
     */
    private function notifyMilestoneReached(Goal $goal, int $milestone): void
    {
        // Notify the goal owner
        NotificationService::sendGoalMilestone(
            $goal->user,
            $goal->title,
            $milestone,
            [
                'goal' => $goal,
                'goal_id' => $goal->id,
                'goal_title' => $goal->title,
                'goal_type' => $goal->type,
                'milestone' => $milestone,
                'current_value' => $goal->current_value,
                'target_value' => $goal->target_value,
                'progress_percentage' => $goal->progress_percentage,
            ]
        );

        // Notify managers for important milestones (50% and 75%)
        if (in_array($milestone, [50, 75])) {
            $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
            foreach ($managersAndAdmins as $user) {
                // Don't notify the goal owner if they're also a manager
                if ($user->id !== $goal->user_id) {
                    NotificationService::createNotification(
                        $user,
                        'goal_milestone',
                        'Goal Milestone Reached',
                        "Goal '{$goal->title}' has reached {$milestone}% progress",
                        [
                            'goal_id' => $goal->id,
                            'goal_title' => $goal->title,
                            'goal_type' => $goal->type,
                            'milestone' => $milestone,
                            'goal_owner' => $goal->user->name,
                            'progress_percentage' => $goal->progress_percentage,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Handle the Goal "deleted" event.
     */
    public function deleted(Goal $goal): void
    {
        try {
            // Check if the goal was failed before deletion
            $progressPercentage = $goal->progress_percentage ?? 0;
            $wasFailed = $goal->status === 'failed' ||
                        ($goal->deadline && now()->isAfter($goal->deadline) && $progressPercentage < 100);

            if ($wasFailed) {
                $this->triggerGoalFailedAlert(
                    $goal,
                    "Failed goal was deleted without completion",
                    [
                        'progress_percentage' => $progressPercentage,
                        'target_value' => $goal->target_value,
                        'current_value' => $goal->current_value,
                        'status_at_deletion' => $goal->status,
                        'goal_owner' => $goal->user->name,
                        'deleted_by' => auth()->user()->name,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send goal deletion alert', [
                'goal_id' => $goal->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
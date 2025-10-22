<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\User;
use App\Services\NotificationService;
use App\Traits\AdministrativeAlertsTrait;
use Illuminate\Support\Facades\Log;

class ExpenseObserver
{
    use AdministrativeAlertsTrait;

    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        try {
            // Notify admins and managers about new expenses for approval
            $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
            
            foreach ($managersAndAdmins as $user) {
                // Don't notify the creator
                if ($user->id !== $expense->user_id) {
                    NotificationService::createNotification(
                        $user,
                        'expense_created',
                        'New Expense for Review',
                        "New expense of {$expense->amount} for {$expense->category} submitted by {$expense->user->name}",
                        [
                            'expense_id' => $expense->id,
                            'amount' => $expense->amount,
                            'category' => $expense->category,
                            'expense_date' => $expense->expense_date->format('Y-m-d'),
                            'submitted_by' => $expense->user->name,
                            'description' => $expense->description,
                            'payment_method' => $expense->payment_method,
                            'merchant' => $expense->merchant,
                        ]
                    );
                }
            }

            // Notify the expense creator that it's under review
            NotificationService::createNotification(
                $expense->user,
                'expense_submitted',
                'Expense Submitted for Review',
                "Your expense of {$expense->amount} for {$expense->category} has been submitted for review",
                [
                    'expense_id' => $expense->id,
                    'amount' => $expense->amount,
                    'category' => $expense->category,
                    'expense_date' => $expense->expense_date->format('Y-m-d'),
                    'status' => $expense->status,
                ]
            );

            // Check if this is a high-value expense (threshold: $500)
            if ($expense->amount >= 500) {
                foreach ($managersAndAdmins as $user) {
                    NotificationService::createNotification(
                        $user,
                        'high_value_expense',
                        'High Value Expense',
                        "High-value expense of {$expense->amount} requires immediate attention",
                        [
                            'expense_id' => $expense->id,
                            'amount' => $expense->amount,
                            'category' => $expense->category,
                            'submitted_by' => $expense->user->name,
                            'threshold' => 500,
                            'expense_date' => $expense->expense_date->format('Y-m-d'),
                        ]
                    );
                }
                
                // Trigger unusual expense alert for high-value expenses
                $this->triggerUnusualExpenseAlert(
                    $expense,
                    "High-value expense exceeds threshold of $500",
                    [
                        'threshold' => 500,
                        'expense_date' => $expense->expense_date->format('Y-m-d'),
                        'payment_method' => $expense->payment_method,
                        'merchant' => $expense->merchant,
                    ]
                );
            }
            
            // Check for unusual expense patterns
            $this->checkUnusualExpensePatterns($expense);
        } catch (\Exception $e) {
            Log::error('Failed to send expense creation notification', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updated(Expense $expense): void
    {
        try {
            $changes = $expense->getChanges();
            
            // Check if expense status was updated
            if (isset($changes['status'])) {
                $oldStatus = $expense->getOriginal('status');
                $newStatus = $expense->status;

                // Notify the expense creator about status change
                NotificationService::createNotification(
                    $expense->user,
                    'expense_status_updated',
                    'Expense Status Updated',
                    "Your expense status has been updated to {$newStatus}",
                    [
                        'expense_id' => $expense->id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'amount' => $expense->amount,
                        'category' => $expense->category,
                        'updated_by' => auth()->user()->name,
                    ]
                );

                // If approved, send approval notification
                if ($newStatus === 'paid') {
                    NotificationService::sendExpenseApproval(
                        $expense->user,
                        $expense->amount,
                        $expense->category,
                        [
                            'expense' => $expense,
                            'expense_id' => $expense->id,
                            'approved_by' => auth()->user()->name,
                            'approved_at' => now()->format('Y-m-d H:i:s'),
                            'payment_method' => $expense->payment_method,
                        ]
                    );

                    // Notify admins/managers about the approval
                    $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
                    foreach ($managersAndAdmins as $user) {
                        // Don't notify the person who approved it
                        if ($user->id !== auth()->id()) {
                            NotificationService::createNotification(
                                $user,
                                'expense_approved',
                                'Expense Approved',
                                "Expense of {$expense->amount} for {$expense->category} approved by " . auth()->user()->name,
                                [
                                    'expense_id' => $expense->id,
                                    'amount' => $expense->amount,
                                    'category' => $expense->category,
                                    'approved_by' => auth()->user()->name,
                                    'expense_user' => $expense->user->name,
                                ]
                            );
                        }
                    }
                }

                // If rejected, send rejection notification
                if ($newStatus === 'rejected') {
                    NotificationService::sendExpenseRejection(
                        $expense->user,
                        $expense->amount,
                        $expense->category,
                        [
                            'expense' => $expense,
                            'expense_id' => $expense->id,
                            'amount' => $expense->amount,
                            'category' => $expense->category,
                            'rejected_by' => auth()->user()->name,
                            'rejected_at' => now()->format('Y-m-d H:i:s'),
                            'notes' => $expense->notes,
                        ]
                    );

                    // Notify admins/managers about the rejection
                    $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
                    foreach ($managersAndAdmins as $user) {
                        // Don't notify the person who rejected it
                        if ($user->id !== auth()->id()) {
                            NotificationService::createNotification(
                                $user,
                                'expense_rejected',
                                'Expense Rejected',
                                "Expense of {$expense->amount} rejected by " . auth()->user()->name,
                                [
                                    'expense_id' => $expense->id,
                                    'amount' => $expense->amount,
                                    'category' => $expense->category,
                                    'rejected_by' => auth()->user()->name,
                                    'expense_user' => $expense->user->name,
                                ]
                            );
                        }
                    }
                }
            }

            // Check if expense amount was updated significantly
            if (isset($changes['amount'])) {
                $oldAmount = $expense->getOriginal('amount');
                $difference = abs($expense->amount - $oldAmount);
                
                // Notify if amount changed by more than $50
                if ($difference >= 50) {
                    $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
                    
                    foreach ($managersAndAdmins as $user) {
                        NotificationService::createNotification(
                            $user,
                            'expense_amount_updated',
                            'Expense Amount Updated',
                            "Expense amount updated from {$oldAmount} to {$expense->amount}",
                            [
                                'expense_id' => $expense->id,
                                'old_amount' => $oldAmount,
                                'new_amount' => $expense->amount,
                                'difference' => $difference,
                                'category' => $expense->category,
                                'updated_by' => auth()->user()->name,
                            ]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send expense update notification', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Check for unusual expense patterns
     */
    private function checkUnusualExpensePatterns(Expense $expense): void
    {
        try {
            // Check for expenses on weekends/holidays
            if ($expense->expense_date->isWeekend()) {
                $this->triggerUnusualExpenseAlert(
                    $expense,
                    "Expense submitted on weekend",
                    [
                        'pattern_type' => 'weekend_expense',
                        'day_of_week' => $expense->expense_date->dayName,
                    ]
                );
            }
            
            // Check for expenses outside business hours (before 8am or after 6pm)
            $expenseHour = now()->hour;
            if ($expenseHour < 8 || $expenseHour > 18) {
                $this->triggerUnusualExpenseAlert(
                    $expense,
                    "Expense submitted outside business hours",
                    [
                        'pattern_type' => 'after_hours_expense',
                        'submission_hour' => $expenseHour,
                    ]
                );
            }
            
            // Check for unusual categories (you might want to define these in config)
            $unusualCategories = ['entertainment', 'gifts', 'personal', 'travel'];
            if (in_array(strtolower($expense->category), $unusualCategories)) {
                $this->triggerUnusualExpenseAlert(
                    $expense,
                    "Expense in unusual category: {$expense->category}",
                    [
                        'pattern_type' => 'unusual_category',
                        'category' => $expense->category,
                    ]
                );
            }
            
            // Check for duplicate expenses (same amount, same category, same date)
            $duplicateExpenses = Expense::where('user_id', $expense->user_id)
                ->where('amount', $expense->amount)
                ->where('category', $expense->category)
                ->where('expense_date', $expense->expense_date)
                ->where('id', '!=', $expense->id)
                ->count();
                
            if ($duplicateExpenses > 0) {
                $this->triggerUnusualExpenseAlert(
                    $expense,
                    "Potential duplicate expense detected",
                    [
                        'pattern_type' => 'duplicate_expense',
                        'duplicate_count' => $duplicateExpenses,
                    ]
                );
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to check unusual expense patterns', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
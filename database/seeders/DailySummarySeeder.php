<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DailySummary;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class DailySummarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Create daily summaries for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now()->subDay(); // Yesterday

        foreach ($users as $user) {
            $currentDate = clone $startDate;
            
            while ($currentDate <= $endDate) {
                // Skip weekends for some users (simulating different work patterns)
                if ($currentDate->isWeekend() && $user->role === 'VA' && rand(0, 1)) {
                    $currentDate->addDay();
                    continue;
                }

                // Generate realistic daily summary data
                $totalTasks = rand(3, 12);
                $completedTasks = rand(0, $totalTasks);
                $productivityScore = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
                
                // Generate notes based on performance
                $notes = $this->generateNotes($completedTasks, $totalTasks, $productivityScore, $user->role);

                $summary = [
                    'user_id' => $user->id,
                    'date' => $currentDate->toDateString(),
                    'total_tasks' => $totalTasks,
                    'completed_tasks' => $completedTasks,
                    'productivity_score' => $productivityScore,
                    'notes' => $notes,
                    'created_at' => $currentDate->copy()->addHours(18), // Created at 6 PM
                    'updated_at' => $currentDate->copy()->addHours(18),
                ];

                DailySummary::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $currentDate->toDateString(),
                    ],
                    $summary
                );
                $currentDate->addDay();
            }
        }
    }

    /**
     * Generate notes based on performance and role
     */
    private function generateNotes(int $completed, int $total, float $score, string $role): string
    {
        $completionRate = $total > 0 ? ($completed / $total) * 100 : 0;
        
        // Base notes on performance
        if ($completionRate >= 90) {
            $baseNotes = [
                'Excellent productivity today! All major tasks completed.',
                'Outstanding performance! Exceeded expectations.',
                'Highly productive day. Great time management.',
                'Exceptional work completed. Keep up the great performance!'
            ];
        } elseif ($completionRate >= 75) {
            $baseNotes = [
                'Good progress today. Most tasks completed successfully.',
                'Productive day with solid accomplishments.',
                'Good momentum maintained throughout the day.',
                'Steady progress on daily objectives.'
            ];
        } elseif ($completionRate >= 50) {
            $baseNotes = [
                'Moderate progress. Some tasks need attention tomorrow.',
                'Mixed results today. Focus on priorities.',
                'Partial completion. Better planning needed.',
                'Some achievements, room for improvement.'
            ];
        } else {
            $baseNotes = [
                'Challenging day. Several tasks pending.',
                'Limited progress. Need to address blockers.',
                'Difficult day. Reassess priorities for tomorrow.',
                'Low productivity. Identify and resolve issues.'
            ];
        }

        // Add role-specific context
        $roleContext = '';
        if ($role === 'Admin') {
            $roleContext = ' Administrative tasks and team management activities.';
        } elseif ($role === 'Manager') {
            $roleContext = ' Team coordination and client management.';
        } else { // VA
            $roleContext = ' Task execution and daily operations.';
        }

        return Arr::random($baseNotes) . $roleContext;
    }
}
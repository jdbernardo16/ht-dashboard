<?php

namespace Database\Seeders;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class GoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereIn('role', ['admin', 'manager', 'va'])->get();

        $goalTypes = ['sales', 'revenue', 'expense', 'task', 'content', 'other'];
        $statuses = ['not_started', 'in_progress', 'completed', 'failed'];
        $priorityOptions = ['low', 'medium', 'high', 'urgent'];

        $goalTitles = [
            'Increase Monthly Revenue by 25%',
            'Acquire 100 New Customers This Quarter',
            'Launch New Product Line by Q4',
            'Expand into New Market Segment',
            'Reduce Operational Costs by 15%',
            'Increase Brand Awareness by 30%',
            'Build High-Performance Team',
            'Achieve 90% Customer Satisfaction',
            'Increase Website Traffic by 50%',
            'Launch Mobile App by Year End',
            'Achieve 20% Profit Margin',
            'Reduce Customer Churn by 10%',
            'Increase Social Media Following by 1000',
            'Launch New Service Offering',
            'Achieve 95% Employee Satisfaction',
            'Increase Average Order Value by 30%',
            'Launch New Marketing Campaign',
            'Achieve 80% Lead Conversion Rate',
            'Reduce Time-to-Market by 25%',
            'Increase Customer Lifetime Value by 40%',
        ];

        $goalDescriptions = [
            'Increase monthly revenue by 25% through targeted marketing campaigns and sales optimization.',
            'Acquire 100 new customers this quarter through strategic lead generation and conversion optimization.',
            'Launch new product line by Q4 through comprehensive market research and development.',
            'Expand into new market segment through strategic partnerships and market analysis.',
            'Reduce operational costs by 15% through process optimization and resource management.',
            'Increase brand awareness by 30% through comprehensive marketing campaigns and PR initiatives.',
            'Build high-performance team through strategic hiring and training programs.',
            'Achieve 90% customer satisfaction through improved service delivery and feedback management.',
            'Increase website traffic by 50% through SEO optimization and content marketing.',
            'Launch mobile app by year end through comprehensive development and testing.',
            'Achieve 20% profit margin through cost optimization and revenue growth.',
            'Reduce customer churn by 10% through improved service delivery and retention strategies.',
            'Increase social media following by 1000 through strategic content creation and engagement.',
            'Launch new service offering through comprehensive market research and development.',
            'Achieve 95% employee satisfaction through improved workplace culture and benefits.',
            'Increase average order value by 30% through upselling and cross-selling strategies.',
            'Launch new marketing campaign through strategic planning and execution.',
            'Achieve 80% lead conversion rate through improved sales processes and follow-up.',
            'Reduce time-to-market by 25% through process optimization and resource management.',
            'Increase customer lifetime value by 40% through improved service delivery and retention strategies.',
        ];


        foreach ($users as $user) {
            // Create 10-15 goals per user
            $goalsCount = rand(10, 15);

            for ($i = 0; $i < $goalsCount; $i++) {
                $dueDate = Carbon::createFromTimestamp(
                    rand(
                        Carbon::now()->subMonths(6)->timestamp,
                        Carbon::now()->addMonths(6)->timestamp
                    )
                );

                $createdDate = Carbon::createFromTimestamp(
                    rand(
                        Carbon::now()->subMonths(7)->timestamp,
                        $dueDate->timestamp - 86400
                    )
                );

                $type = $goalTypes[array_rand($goalTypes)];
                $status = $statuses[array_rand($statuses)];

                $quarter = (string) ceil($dueDate->month / 3);
                $year = $dueDate->year;
                $priorityVal = $priorityOptions[array_rand($priorityOptions)];

                $goal = Goal::create([
                    'user_id' => $user->id,
                    'title' => $goalTitles[array_rand($goalTitles)],
                    'description' => $goalDescriptions[array_rand($goalDescriptions)],
                    'type' => $type,
                    'target_value' => rand(1000, 100000),
                    'current_value' => rand(0, 50000),
                    'quarter' => $quarter,
                    'year' => $year,
                    'deadline' => $dueDate,
                    'status' => $status,
                    'priority' => $priorityVal,
                    'created_at' => $createdDate,
                    'updated_at' => $createdDate,
                ]);
            }
        }

        // Create some high-value goals
        $highValueGoals = [
            [
                'user_id' => User::where('role', 'admin')->first()->id,
                'title' => 'Increase Annual Revenue by 50%',
                'description' => 'Implement comprehensive revenue growth strategy including new product launches, market expansion, and sales optimization.',
                'type' => 'revenue',
                'target_value' => 500000,
                'current_value' => 250000,
                'quarter' => (string) ceil(Carbon::now()->addMonths(9)->month / 3),
                'year' => Carbon::now()->addMonths(9)->year,
                'deadline' => Carbon::now()->addMonths(9),
                'status' => 'in_progress',
                'priority' => 'urgent',
                'created_at' => Carbon::now()->subMonths(3),
                'updated_at' => Carbon::now()->subMonths(3),
            ],
            [
                'user_id' => User::where('role', 'manager')->first()->id,
                'title' => 'Launch New Product Line',
                'description' => 'Complete product development and launch new product line by Q4 through comprehensive market research and development.',
                'type' => 'other',
                'target_value' => 100000,
                'current_value' => 25000,
                'quarter' => (string) ceil(Carbon::now()->addMonths(4)->month / 3),
                'year' => Carbon::now()->addMonths(4)->year,
                'deadline' => Carbon::now()->addMonths(4),
                'status' => 'in_progress',
                'priority' => 'high',
                'created_at' => Carbon::now()->subMonths(2),
                'updated_at' => Carbon::now()->subMonths(2),
            ],
        ];

        foreach ($highValueGoals as $goal) {
            Goal::create($goal);
        }
    }

    /**
     * Generate goal notes based on status
     */
    private function generateGoalNotes(string $status): string
    {
        $notes = [
            'not_started' => 'Goal is waiting to be started',
            'in_progress' => 'Currently working on this goal',
            'completed' => 'Goal completed successfully',
            'failed' => 'Goal failed or was cancelled due to changing priorities',
            'cancelled' => 'Goal was cancelled due to changing priorities',
            'on_hold' => 'Goal temporarily on hold pending further information',
        ];

        return $notes[$status] ?? 'No additional notes';
    }
}

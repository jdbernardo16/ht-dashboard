<?php

namespace App\Http\Controllers;

use App\Models\DailySummary;
use App\Models\Task;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\ContentPost;
use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get daily summary data for dashboard
     *
     * @return JsonResponse
     */
    public function getDailySummary(): JsonResponse
    {
        $today = Carbon::today();
        
        // Get today's tasks
        $tasks = Task::whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_tasks,
                SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress_tasks
            ')
            ->first();

        // Get today's revenue from sales
        $revenue = Sale::whereDate('created_at', $today)
            ->sum('amount');

        // Get daily summary record
        $dailySummary = DailySummary::whereDate('date', $today)->first();

        return response()->json([
            'tasks' => [
                'total' => $tasks->total_tasks ?? 0,
                'completed' => $tasks->completed_tasks ?? 0,
                'pending' => $tasks->pending_tasks ?? 0,
                'in_progress' => $tasks->in_progress_tasks ?? 0,
            ],
            'revenue' => [
                'today' => $revenue ?? 0,
                'target' => $dailySummary->revenue_target ?? 5000,
                'percentage' => $dailySummary && $dailySummary->revenue_target > 0 
                    ? round(($revenue / $dailySummary->revenue_target) * 100, 1) 
                    : 0,
            ],
            'last_updated' => now()->toISOString(),
        ]);
    }

    /**
     * Get activity distribution data
     *
     * @return JsonResponse
     */
    public function getActivityDistribution(): JsonResponse
    {
        $startDate = Carbon::now()->subDays(7);
        
        // Get activity breakdown by type
        $activities = Task::where('created_at', '>=', $startDate)
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->map(function ($activity) {
                return [
                    'name' => ucfirst(str_replace('_', ' ', $activity->type)),
                    'value' => $activity->count,
                    'color' => $this->getActivityColor($activity->type),
                ];
            });

        // Get daily activity trend
        $dailyTrend = Task::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            });

        return response()->json([
            'distribution' => $activities,
            'daily_trend' => $dailyTrend,
            'total_activities' => $activities->sum('value'),
            'period' => 'Last 7 days',
        ]);
    }

    /**
     * Get sales metrics data
     *
     * @return JsonResponse
     */
    public function getSalesMetrics(): JsonResponse
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Monthly sales data
        $monthlySales = Sale::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as daily_sales')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'sales' => $item->daily_sales,
                ];
            });

        // Sales summary
        $salesSummary = Sale::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->selectRaw('
                COUNT(*) as total_sales,
                SUM(amount) as total_revenue,
                AVG(amount) as average_sale,
                MAX(amount) as highest_sale
            ')
            ->first();

        // Top products/services
        $topProducts = Sale::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->select('product_name', DB::raw('SUM(amount) as total_sales'))
            ->groupBy('product_name')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'sales' => $item->total_sales,
                ];
            });

        return response()->json([
            'monthly_sales' => $monthlySales,
            'summary' => [
                'total_sales' => $salesSummary->total_sales ?? 0,
                'total_revenue' => $salesSummary->total_revenue ?? 0,
                'average_sale' => $salesSummary->average_sale ?? 0,
                'highest_sale' => $salesSummary->highest_sale ?? 0,
            ],
            'top_products' => $topProducts,
            'month' => Carbon::now()->format('F Y'),
        ]);
    }

    /**
     * Get expenses data
     *
     * @return JsonResponse
     */
    public function getExpenses(): JsonResponse
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Monthly expenses by category
        $expensesByCategory = Expense::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->select('category', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category')
            ->get()
            ->map(function ($expense) {
                return [
                    'category' => ucfirst($expense->category),
                    'amount' => $expense->total_amount,
                    'color' => $this->getExpenseColor($expense->category),
                ];
            });

        // Daily expenses trend
        $dailyExpenses = Expense::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as daily_expense')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'expense' => $item->daily_expense,
                ];
            });

        // Expense summary
        $totalExpenses = Expense::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount');

        $budget = 5000; // Monthly budget
        $budgetRemaining = max(0, $budget - $totalExpenses);
        $budgetUsedPercentage = $budget > 0 ? round(($totalExpenses / $budget) * 100, 1) : 0;

        return response()->json([
            'expenses_by_category' => $expensesByCategory,
            'daily_expenses' => $dailyExpenses,
            'summary' => [
                'total_expenses' => $totalExpenses,
                'budget' => $budget,
                'remaining' => $budgetRemaining,
                'percentage_used' => $budgetUsedPercentage,
            ],
            'month' => Carbon::now()->format('F Y'),
        ]);
    }

    /**
     * Get content statistics
     *
     * @return JsonResponse
     */
    public function getContentStats(): JsonResponse
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Content performance by type
        $contentByType = ContentPost::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->map(function ($content) {
                return [
                    'type' => ucfirst($content->type),
                    'count' => $content->count,
                ];
            });

        // Recent content with engagement
        $recentContent = ContentPost::with('engagements')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($content) {
                return [
                    'title' => $content->title,
                    'type' => $content->type,
                    'views' => $content->views ?? 0,
                    'likes' => $content->likes ?? 0,
                    'shares' => $content->shares ?? 0,
                    'created_at' => $content->created_at->toDateString(),
                ];
            });

        // Content performance summary
        $totalContent = ContentPost::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $totalViews = ContentPost::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('views');

        return response()->json([
            'content_by_type' => $contentByType,
            'recent_content' => $recentContent,
            'summary' => [
                'total_content' => $totalContent,
                'total_views' => $totalViews,
                'average_views' => $totalContent > 0 ? round($totalViews / $totalContent) : 0,
            ],
            'month' => Carbon::now()->format('F Y'),
        ]);
    }

    /**
     * Get quarterly goals progress
     *
     * @return JsonResponse
     */
    public function getQuarterlyGoals(): JsonResponse
    {
        $currentQuarter = Carbon::now()->quarter;
        $currentYear = Carbon::now()->year;
        
        // Get all goals for current quarter
        $goals = Goal::where('quarter', $currentQuarter)
            ->where('year', $currentYear)
            ->get()
            ->map(function ($goal) {
                $progressPercentage = $goal->target > 0 
                    ? round(($goal->current / $goal->target) * 100, 1) 
                    : 0;

                return [
                    'id' => $goal->id,
                    'title' => $goal->title,
                    'description' => $goal->description,
                    'target' => $goal->target,
                    'current' => $goal->current,
                    'progress_percentage' => $progressPercentage,
                    'status' => $this->getGoalStatus($progressPercentage),
                    'category' => $goal->category,
                    'deadline' => $goal->deadline->toDateString(),
                ];
            });

        // Quarterly summary
        $totalGoals = $goals->count();
        $completedGoals = $goals->where('status', 'completed')->count();
        $inProgressGoals = $goals->where('status', 'in_progress')->count();
        $notStartedGoals = $goals->where('status', 'not_started')->count();

        return response()->json([
            'goals' => $goals,
            'summary' => [
                'total_goals' => $totalGoals,
                'completed_goals' => $completedGoals,
                'in_progress_goals' => $inProgressGoals,
                'not_started_goals' => $notStartedGoals,
                'overall_progress' => $totalGoals > 0 
                    ? round($goals->avg('progress_percentage'), 1) 
                    : 0,
            ],
            'quarter' => "Q{$currentQuarter} {$currentYear}",
        ]);
    }

    /**
     * Get dashboard data for all modules
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'daily_summary' => $this->getDailySummary()->getData(),
            'activity_distribution' => $this->getActivityDistribution()->getData(),
            'sales_metrics' => $this->getSalesMetrics()->getData(),
            'expenses' => $this->getExpenses()->getData(),
            'content_stats' => $this->getContentStats()->getData(),
            'quarterly_goals' => $this->getQuarterlyGoals()->getData(),
            'last_updated' => now()->toISOString(),
        ]);
    }

    /**
     * Helper method to get activity color
     */
    private function getActivityColor(string $type): string
    {
        $colors = [
            'task' => '#3b82f6',
            'meeting' => '#8b5cf6',
            'call' => '#10b981',
            'email' => '#f59e0b',
            'research' => '#ef4444',
            'planning' => '#6366f1',
        ];

        return $colors[$type] ?? '#6b7280';
    }

    /**
     * Helper method to get expense color
     */
    private function getExpenseColor(string $category): string
    {
        $colors = [
            'marketing' => '#3b82f6',
            'operations' => '#8b5cf6',
            'salaries' => '#10b981',
            'software' => '#f59e0b',
            'travel' => '#ef4444',
            'utilities' => '#6366f1',
            'other' => '#6b7280',
        ];

        return $colors[strtolower($category)] ?? '#6b7280';
    }

    /**
     * Helper method to get goal status
     */
    private function getGoalStatus(float $progress): string
    {
        if ($progress >= 100) {
            return 'completed';
        } elseif ($progress > 0) {
            return 'in_progress';
        } else {
            return 'not_started';
        }
    }
}
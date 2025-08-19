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
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with all module data
     *
     * @return Response
     */
    public function dashboard(): Response
    {
        return Inertia::render('Dashboard', [
            'dashboardData' => [
                'dailySummary' => $this->gatherDailySummary(),
                'activityDistribution' => $this->gatherActivityDistribution(),
                'salesMetrics' => $this->gatherSalesMetrics(),
                'expenses' => $this->gatherExpenses(),
                'contentStats' => $this->gatherContentStats(),
                'quarterlyGoals' => $this->gatherQuarterlyGoals(),
            ],
            'lastUpdated' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get daily summary data for dashboard
     *
     * @return JsonResponse
     */
    public function getDailySummary(): JsonResponse
    {
        return response()->json($this->gatherDailySummary());
    }

    /**
     * Internal helper that returns array data for daily summary
     *
     * @return array
     */
    private function gatherDailySummary(): array
    {
        $today = Carbon::today();
        $todayDate = $today->toDateString();

        // Get today's tasks (aggregates always return a row)
        $tasks = Task::whereDate('created_at', $todayDate)
            ->selectRaw("
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks
            ")
            ->first();

        // Get today's revenue from sales
        $revenue = (float) Sale::whereDate('created_at', $todayDate)->sum('amount');

        // Get daily summary record (may be null)
        $dailySummary = DailySummary::whereDate('date', $todayDate)->first();

        $target = optional($dailySummary)->revenue_target ?? 5000;
        $percentage = ($target > 0) ? round(($revenue / $target) * 100, 1) : 0;

        return [
            'tasks' => [
                'total' => (int) (optional($tasks)->total_tasks ?? 0),
                'completed' => (int) (optional($tasks)->completed_tasks ?? 0),
                'pending' => (int) (optional($tasks)->pending_tasks ?? 0),
                'in_progress' => (int) (optional($tasks)->in_progress_tasks ?? 0),
            ],
            'revenue' => [
                'today' => $revenue,
                'target' => (float) $target,
                'percentage' => $percentage,
                // Provide a 'change' field for the frontend (percent change vs yesterday) — default to 0
                'change' => 0,
            ],
            // Provide UI-friendly defaults expected by the Vue DailySummaryModule
            'projects' => [
                'active' => 0,
                'completed' => 0,
            ],
            'hoursWorked' => 0,
            'meetings' => 0,
            'emails' => 0,
            'deadlines' => 0,
            'last_updated' => now()->toIso8601String(),
        ];
    }

    /**
     * Get activity distribution data
     *
     * @return JsonResponse
     */
    public function getActivityDistribution(): JsonResponse
    {
        return response()->json($this->gatherActivityDistribution());
    }

    private function gatherActivityDistribution(): array
    {
        // Last 7 days (including today)
        $startDate = Carbon::today()->subDays(6)->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        // Get activity breakdown by status (since we don't have type column)
        $activities = Task::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($activity) {
                $status = $activity->status ?? 'other';
                return [
                    'name' => ucfirst(str_replace('_', ' ', $status)),
                    'value' => (int) $activity->count,
                    'color' => $this->getActivityColor($status),
                ];
            });

        // Get daily activity trend
        $dailyTrend = Task::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ];
            });

        return [
            'distribution' => $activities,
            'daily_trend' => $dailyTrend,
            'total_activities' => $activities->sum('value'),
            'period' => 'Last 7 days',
        ];
    }

    /**
     * Get sales metrics data
     *
     * @return JsonResponse
     */
    public function getSalesMetrics(): JsonResponse
    {
        return response()->json($this->gatherSalesMetrics());
    }

    private function gatherSalesMetrics(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Monthly sales data
        $monthlySales = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as daily_sales'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'sales' => (float) $item->daily_sales,
                ];
            });

        // Sales summary (use COALESCE via DB if you prefer, but optional() below is safe)
        $salesSummary = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('COUNT(*) as total_sales, SUM(amount) as total_revenue, AVG(amount) as average_sale, MAX(amount) as highest_sale')
            ->first();

        // Top products/services
        $topProducts = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select('product_name', DB::raw('SUM(amount) as total_sales'))
            ->groupBy('product_name')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'sales' => (float) $item->total_sales,
                ];
            });

        return [
            'monthly_sales' => $monthlySales,
            'summary' => [
                'total_sales' => (int) optional($salesSummary)->total_sales ?? 0,
                'total_revenue' => (float) optional($salesSummary)->total_revenue ?? 0,
                'average_sale' => (float) optional($salesSummary)->average_sale ?? 0,
                'highest_sale' => (float) optional($salesSummary)->highest_sale ?? 0,
            ],
            'top_products' => $topProducts,
            'month' => Carbon::now()->format('F Y'),
        ];
    }

    /**
     * Get expenses data
     *
     * @return JsonResponse
     */
    public function getExpenses(): JsonResponse
    {
        return response()->json($this->gatherExpenses());
    }

    private function gatherExpenses(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Monthly expenses by category
        $expensesByCategory = Expense::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select('category', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category')
            ->get()
            ->map(function ($expense) {
                $cat = $expense->category ?? 'other';
                return [
                    'category' => ucfirst($cat),
                    'amount' => (float) $expense->total_amount,
                    'color' => $this->getExpenseColor($cat),
                ];
            });

        // Daily expenses trend
        $dailyExpenses = Expense::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as daily_expense'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'expense' => (float) $item->daily_expense,
                ];
            });

        // Expense summary
        $totalExpenses = (float) Expense::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('amount');

        $budget = 5000; // Monthly budget (consider moving to config)
        $budgetRemaining = max(0, $budget - $totalExpenses);
        $budgetUsedPercentage = $budget > 0 ? round(($totalExpenses / $budget) * 100, 1) : 0;

        return [
            'expenses_by_category' => $expensesByCategory,
            'daily_expenses' => $dailyExpenses,
            'summary' => [
                'total_expenses' => $totalExpenses,
                'budget' => $budget,
                'remaining' => $budgetRemaining,
                'percentage_used' => $budgetUsedPercentage,
            ],
            'month' => Carbon::now()->format('F Y'),
        ];
    }

    /**
     * Get content statistics
     *
     * @return JsonResponse
     */
    public function getContentStats(): JsonResponse
    {
        return response()->json($this->gatherContentStats());
    }

    private function gatherContentStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Content performance by type
        $contentByType = ContentPost::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select('content_type', DB::raw('COUNT(*) as count'))
            ->groupBy('content_type')
            ->get()
            ->map(function ($content) {
                return [
                    'type' => ucfirst($content->content_type ?? 'Other'),
                    'count' => (int) $content->count,
                ];
            });

        // Recent content with engagement
        $recentContent = ContentPost::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($content) {
                // engagement_metrics is cast to array on the model. Ensure we handle both string (raw JSON) and array.
                $engagement = is_string($content->engagement_metrics)
                    ? json_decode($content->engagement_metrics, true) ?? []
                    : ($content->engagement_metrics ?? []);

                return [
                    'title' => $content->title ?? 'Untitled',
                    'type' => $content->content_type ?? 'Other',
                    'views' => (int) ($engagement['views'] ?? 0),
                    'likes' => (int) ($engagement['likes'] ?? 0),
                    'shares' => (int) ($engagement['shares'] ?? 0),
                    'created_at' => optional($content->created_at)->toDateString(),
                ];
            });

        // Content performance summary
        $totalContent = (int) ContentPost::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        // Calculate total views from engagement_metrics JSON field
        $totalViews = ContentPost::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->sum(function ($content) {
                // engagement_metrics may already be cast to array by the model; handle both string and array safely
                $engagement = is_string($content->engagement_metrics)
                    ? json_decode($content->engagement_metrics, true) ?? []
                    : ($content->engagement_metrics ?? []);

                return (int) ($engagement['views'] ?? 0);
            });

        return [
            'content_by_type' => $contentByType,
            'recent_content' => $recentContent,
            'summary' => [
                'total_content' => $totalContent,
                'total_views' => $totalViews,
                'average_views' => $totalContent > 0 ? round($totalViews / $totalContent) : 0,
            ],
            'month' => Carbon::now()->format('F Y'),
        ];
    }

    /**
     * Get quarterly goals progress
     *
     * @return JsonResponse
     */
    public function getQuarterlyGoals(): JsonResponse
    {
        return response()->json($this->gatherQuarterlyGoals());
    }

    private function gatherQuarterlyGoals(): array
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
                    'deadline' => optional($goal->deadline)->toDateString(),
                ];
            });

        $totalGoals = $goals->count();
        $completedGoals = $goals->where('status', 'completed')->count();
        $inProgressGoals = $goals->where('status', 'in_progress')->count();
        $notStartedGoals = $goals->where('status', 'not_started')->count();

        return [
            'goals' => $goals,
            'summary' => [
                'total_goals' => $totalGoals,
                'completed_goals' => $completedGoals,
                'in_progress_goals' => $inProgressGoals,
                'not_started_goals' => $notStartedGoals,
                'overall_progress' => $totalGoals > 0 ? round($goals->avg('progress_percentage'), 1) : 0,
            ],
            'quarter' => "Q{$currentQuarter} {$currentYear}",
        ];
    }

    /**
     * Get dashboard data for all modules
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'daily_summary' => $this->gatherDailySummary(),
            'activity_distribution' => $this->gatherActivityDistribution(),
            'sales_metrics' => $this->gatherSalesMetrics(),
            'expenses' => $this->gatherExpenses(),
            'content_stats' => $this->gatherContentStats(),
            'quarterly_goals' => $this->gatherQuarterlyGoals(),
            'last_updated' => now()->toIso8601String(),
        ]);
    }

    /**
     * Helper method to get activity color
     */
    private function getActivityColor(string $status): string
    {
        $colors = [
            'pending' => '#f59e0b',
            'in_progress' => '#3b82f6',
            'completed' => '#10b981',
            'cancelled' => '#ef4444',
        ];

        return $colors[$status] ?? '#6b7280';
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

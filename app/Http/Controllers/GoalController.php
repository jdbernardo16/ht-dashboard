<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class GoalController extends Controller
{
    /**
     * Display a listing of the goals.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Goal::class);

        $query = Goal::with(['user']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            // Skip status filtering since it's not in the database schema
            // $query->where('status', $request->get('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('deadline', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('deadline', '<=', $request->get('date_to'));
        }

        // Role-based filtering
        if (Auth::user()->role === 'va') {
            $query->where('user_id', Auth::id());
        }

        $goals = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Format the goals data to match frontend expectations
        $goals->getCollection()->transform(function ($goal) {
            return [
                'id' => $goal->id,
                'title' => $goal->title,
                'description' => $goal->description,
                'type' => $goal->type,
                'target_value' => $goal->target_value,
                'current_value' => $goal->current_value,
                'progress' => $goal->progress,
                'priority' => $goal->priority,
                'status' => $goal->status,
                'target_date' => $goal->deadline,
                'created_at' => $goal->created_at,
                'updated_at' => $goal->updated_at,
                'user' => $goal->user,
            ];
        });

        return Inertia::render('Goals/Index', [
            'goals' => $goals,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to'])
        ]);
    }

    /**
     * Show the form for creating a new goal.
     */
    public function create()
    {
        Gate::authorize('create', Goal::class);

        return Inertia::render('Goals/Create');
    }

    /**
     * Store a newly created goal in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Goal::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_value' => 'required|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'quarter' => 'required|string|in:Q1,Q2,Q3,Q4',
            'year' => 'required|integer|min:2020|max:2030',
            'deadline' => 'required|date|after_or_equal:today',
            'type' => 'nullable|string|max:255',
            'priority' => 'nullable|string|in:low,medium,high',
            'status' => 'nullable|string|in:not_started,in_progress,completed,on_hold,cancelled',
            'progress' => 'nullable|numeric|min:0|max:100',
        ]);

        // Ensure progress has a default value if not provided
        $validated['progress'] = $validated['progress'] ?? 0;

        $validated['user_id'] = Auth::id();

        $goal = Goal::create($validated);

        return redirect()->route('goals.index')
            ->with('success', 'Goal created successfully');
    }

    /**
     * Display the specified goal.
     */
    public function show(Goal $goal)
    {
        Gate::authorize('view', $goal);

        return Inertia::render('Goals/Show', [
            'goal' => $goal->load(['user'])
        ]);
    }

    /**
     * Show the form for editing the specified goal.
     */
    public function edit(Goal $goal)
    {
        Gate::authorize('update', $goal);

        return Inertia::render('Goals/Edit', [
            'goal' => $goal->load(['user'])
        ]);
    }

    /**
     * Update the specified goal in storage.
     */
    public function update(Request $request, Goal $goal)
    {
        Gate::authorize('update', $goal);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'target_value' => 'sometimes|required|numeric|min:0',
            'current_value' => 'sometimes|nullable|numeric|min:0',
            'quarter' => 'sometimes|required|string|in:Q1,Q2,Q3,Q4',
            'year' => 'sometimes|required|integer|min:2020|max:2030',
            'deadline' => 'sometimes|required|date|after:today',
            'type' => 'sometimes|nullable|string|max:255',
            'priority' => 'sometimes|nullable|string|in:low,medium,high',
            'status' => 'sometimes|nullable|string|in:not_started,in_progress,completed,on_hold,cancelled',
            'progress' => 'sometimes|nullable|numeric|min:0|max:100',
        ]);

        $goal->update($validated);

        return redirect()->route('goals.index')
            ->with('success', 'Goal updated successfully');
    }

    /**
     * Remove the specified goal from storage.
     */
    public function destroy(Goal $goal)
    {
        Gate::authorize('delete', $goal);

        $goal->delete();

        return redirect()->route('goals.index')
            ->with('success', 'Goal deleted successfully');
    }

    /**
     * Get goals statistics
     */
    public function statistics(Request $request)
    {
        Gate::authorize('viewAny', Goal::class);

        $query = Goal::query();

        // Role-based filtering
        if (Auth::user()->role === 'va') {
            $query->where('user_id', Auth::id());
        }

        $totalGoals = $query->count();
        $publishedGoals = $query->clone()->where('status', 'published')->count();
        $draftGoals = $query->clone()->where('status', 'draft')->count();
        $archivedGoals = $query->clone()->where('status', 'archived')->count();

        $totalTargetAmount = $query->sum('target_amount');
        $totalCurrentAmount = $query->sum('current_amount');
        $completionRate = $totalTargetAmount > 0 ? ($totalCurrentAmount / $totalTargetAmount) * 100 : 0;

        $monthlyGoals = $query->clone()
            ->selectRaw('COUNT(*) as total, SUM(target_amount) as target_sum, SUM(current_amount) as current_sum, MONTH(created_at) as month')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    \Carbon\Carbon::create()->month($item->month)->format('F') => [
                        'total' => $item->total,
                        'target_sum' => $item->target_sum,
                        'current_sum' => $item->current_sum,
                    ]
                ];
            });

        return Inertia::render('Goals/Statistics', [
            'statistics' => [
                'total_goals' => $totalGoals,
                'published_goals' => $publishedGoals,
                'draft_goals' => $draftGoals,
                'archived_goals' => $archivedGoals,
                'total_target_amount' => $totalTargetAmount,
                'total_current_amount' => $totalCurrentAmount,
                'completion_rate' => round($completionRate, 2),
                'monthly_goals' => $monthlyGoals
            ]
        ]);
    }
}

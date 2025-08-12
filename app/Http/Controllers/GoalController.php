<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        if ($request->has('date_from')) {
            $query->whereDate('target_date', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('target_date', '<=', $request->get('date_to'));
        }

        // Role-based filtering
        if (Auth::user()->hasRole('virtual_assistant')) {
            $query->where('user_id', Auth::id());
        }

        $goals = $query->orderBy('priority', 'desc')
                      ->orderBy('target_date', 'asc')
                      ->paginate($request->get('per_page', 15));

        return response()->json($goals);
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
            'type' => 'required|in:sales,revenue,expense,task,content,other',
            'target_value' => 'required|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'target_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:not_started,in_progress,completed,failed',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly,monthly,yearly|required_if:is_recurring,true',
            'parent_goal_id' => 'nullable|exists:goals,id',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['progress'] = $validated['current_value'] ?? 0;

        $goal = Goal::create($validated);

        return response()->json([
            'message' => 'Goal created successfully',
            'data' => $goal->load(['user'])
        ], 201);
    }

    /**
     * Display the specified goal.
     */
    public function show(Goal $goal)
    {
        Gate::authorize('view', $goal);

        return response()->json($goal->load(['user']));
    }

    /**
     * Update the specified goal in storage.
     */
    public function update(Request $request, Goal $goal)
    {
        Gate::authorize('update', $goal);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:sales,revenue,expense,task,content,other',
            'target_value' => 'sometimes|required|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'target_date' => 'sometimes|required|date',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'status' => 'sometimes|required|in:not_started,in_progress,completed,failed',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly,monthly,yearly|required_if:is_recurring,true',
            'parent_goal_id' => 'nullable|exists:goals,id',
        ]);

        if (isset($validated['current_value'])) {
            $validated['progress'] = $validated['current_value'];
        }

        $goal->update($validated);

        return response()->json([
            'message' => 'Goal updated successfully',
            'data' => $goal->load(['user'])
        ]);
    }

    /**
     * Remove the specified goal from storage.
     */
    public function destroy(Goal $goal)
    {
        Gate::authorize('delete', $goal);

        $goal->delete();

        return response()->json([
            'message' => 'Goal deleted successfully'
        ], 204);
    }

    /**
     * Update goal progress
     */
    public function updateProgress(Request $request, Goal $goal)
    {
        Gate::authorize('update', $goal);

        $validated = $request->validate([
            'current_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $goal->update([
            'current_value' => $validated['current_value'],
            'progress' => $validated['current_value'],
            'status' => $validated['current_value'] >= $goal->target_value ? 'completed' : 'in_progress'
        ]);

        return response()->json([
            'message' => 'Goal progress updated successfully',
            'data' => $goal->load(['user'])
        ]);
    }

    /**
     * Get goal statistics
     */
    public function statistics(Request $request)
    {
        Gate::authorize('viewAny', Goal::class);

        $query = Goal::query();

        // Role-based filtering
        if (Auth::user()->hasRole('virtual_assistant')) {
            $query->where('user_id', Auth::id());
        }

        $totalGoals = $query->count();
        $completedGoals = $query->clone()->where('status', 'completed')->count();
        $inProgressGoals = $query->clone()->where('status', 'in_progress')->count();
        $failedGoals = $query->clone()->where('status', 'failed')->count();

        $goalsByType = $query->selectRaw('type, COUNT(*) as count')
                           ->groupBy('type')
                           ->pluck('count', 'type');

        $averageProgress = $query->avg('progress') ?? 0;

        return response()->json([
            'total_goals' => $totalGoals,
            'completed_goals' => $completedGoals,
            'in_progress_goals' => $inProgressGoals,
            'failed_goals' => $failedGoals,
            'goals_by_type' => $goalsByType,
            'average_progress' => $averageProgress
        ]);
    }
}
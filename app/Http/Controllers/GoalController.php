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

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Role-based filtering
        if (Auth::user()->hasRole('va')) {
            $query->where('user_id', Auth::id());
        }

        $goals = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15))
            ->withQueryString();

        return Inertia::render('Goals/Index', [
            'goals' => $goals,
            'filters' => $request->only(['search', 'status'])
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
            'description' => 'required|string',
            'target_amount' => 'required|numeric|min:0',
            'target_date' => 'required|date|after:today',
            'status' => 'required|in:draft,published,archived',
            'category' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

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
            'description' => 'sometimes|required|string',
            'target_amount' => 'sometimes|required|numeric|min:0',
            'target_date' => 'sometimes|required|date|after:today',
            'status' => 'sometimes|required|in:draft,published,archived',
            'category' => 'sometimes|required|string|max:255',
            'notes' => 'nullable|string',
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
        if (Auth::user()->hasRole('va')) {
            $query->where('user_id', Auth::id());
        }

        $totalGoals = $query->count();
        $publishedGoals = $query->clone()->where('status', 'published')->count();
        $draftGoals = $query->clone()->where('status', 'draft')->count();
        $archivedGoals = $query->clone()->where('status', 'archived')->count();

        $monthlyGoals = $query->clone()
            ->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->mapWithKeys(function ($value, $key) {
                return [\Carbon\Carbon::create()->month($key)->format('F') => $value];
            });

        return Inertia::render('Goals/Statistics', [
            'statistics' => [
                'total_goals' => $totalGoals,
                'published_goals' => $publishedGoals,
                'draft_goals' => $draftGoals,
                'archived_goals' => $archivedGoals,
                'monthly_goals' => $monthlyGoals
            ]
        ]);
    }
}

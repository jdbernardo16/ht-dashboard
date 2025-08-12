<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     */
    public function index(Request $request)
    {
        return $this->apiIndex($request);
    }

    /**
     * API endpoint for tasks
     */
    public function apiIndex(Request $request)
    {
        Gate::authorize('viewAny', Task::class);

        $query = Task::with(['user', 'assignedTo']);

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

        if ($request->has('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->get('assigned_to'));
        }

        if ($request->has('date_from')) {
            $query->whereDate('due_date', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('due_date', '<=', $request->get('date_to'));
        }

        // Role-based filtering
        if (Auth::user()->hasRole('virtual_assistant')) {
            $query->where(function ($q) {
                $q->where('user_id', Auth::id())
                  ->orWhere('assigned_to', Auth::id());
            });
        }

        $tasks = $query->orderBy('priority', 'desc')
                      ->orderBy('due_date', 'asc')
                      ->paginate($request->get('per_page', 15));

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['search', 'status', 'priority', 'assigned_to', 'date_from', 'date_to'])
        ]);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Task::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'due_date' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
            'category' => 'nullable|string|max:100',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'notes' => 'nullable|string',
            'parent_task_id' => 'nullable|exists:tasks,id',
            'related_goal_id' => 'nullable|exists:goals,id',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly,monthly,yearly|required_if:is_recurring,true',
        ]);

        $validated['user_id'] = Auth::id();

        // Handle tags
        if (isset($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        }

        $task = Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        Gate::authorize('view', $task);

        return Inertia::render('Tasks/Show', [
            'task' => $task->load(['user', 'assignedTo'])
        ]);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'status' => 'sometimes|required|in:pending,in_progress,completed,cancelled',
            'due_date' => 'sometimes|required|date',
            'assigned_to' => 'nullable|exists:users,id',
            'category' => 'nullable|string|max:100',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'notes' => 'nullable|string',
            'parent_task_id' => 'nullable|exists:tasks,id',
            'related_goal_id' => 'nullable|exists:goals,id',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly,monthly,yearly|required_if:is_recurring,true',
        ]);

        // Handle tags
        if (isset($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        }

        $task->update($validated);

        return redirect()->back()->with('success', 'Task updated successfully');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully');
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'actual_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $task->update($validated);

        return redirect()->back()->with('success', 'Task status updated successfully');
    }

    /**
     * Get task statistics
     */
    public function statistics(Request $request)
    {
        Gate::authorize('viewAny', Task::class);

        $query = Task::query();

        // Role-based filtering
        if (Auth::user()->hasRole('virtual_assistant')) {
            $query->where(function ($q) {
                $q->where('user_id', Auth::id())
                  ->orWhere('assigned_to', Auth::id());
            });
        }

        $totalTasks = $query->count();
        $completedTasks = $query->clone()->where('status', 'completed')->count();
        $pendingTasks = $query->clone()->where('status', 'pending')->count();
        $inProgressTasks = $query->clone()->where('status', 'in_progress')->count();

        $tasksByPriority = $query->selectRaw('priority, COUNT(*) as count')
                               ->groupBy('priority')
                               ->pluck('count', 'priority');

        $overdueTasks = $query->clone()
                            ->where('due_date', '<', now())
                            ->whereIn('status', ['pending', 'in_progress'])
                            ->count();

        $totalEstimatedHours = $query->sum('estimated_hours');
        $totalActualHours = $query->sum('actual_hours');

        return Inertia::render('Tasks/Statistics', [
            'statistics' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'in_progress_tasks' => $inProgressTasks,
                'overdue_tasks' => $overdueTasks,
                'tasks_by_priority' => $tasksByPriority,
                'total_estimated_hours' => $totalEstimatedHours,
                'total_actual_hours' => $totalActualHours
            ]
        ]);
    }

    /**
     * Get tasks assigned to authenticated user
     */
    public function myTasks(Request $request)
    {
        $query = Task::with(['user', 'assignedTo'])
                    ->where('assigned_to', Auth::id());

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        $tasks = $query->orderBy('priority', 'desc')
                      ->orderBy('due_date', 'asc')
                      ->paginate($request->get('per_page', 15));

        return Inertia::render('Tasks/MyTasks', [
            'tasks' => $tasks,
            'filters' => $request->only(['status', 'priority'])
        ]);
    }
}
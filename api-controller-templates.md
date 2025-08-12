# API Controller Templates

## Base Controller Template

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BaseApiController extends Controller
{
    /**
     * Return success response
     */
    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => '1.0'
            ]
        ], $code);
    }

    /**
     * Return error response
     */
    protected function errorResponse($message, $errors = [], $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => '1.0'
            ]
        ], $code);
    }

    /**
     * Return paginated response
     */
    protected function paginatedResponse($data, $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'message' => $message,
            'meta' => [
                'current_page' => $data->currentPage(),
                'from' => $data->firstItem(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'to' => $data->lastItem(),
                'total' => $data->total(),
                'timestamp' => now()->toISOString(),
                'version' => '1.0'
            ]
        ]);
    }
}
```

## Sale Controller Template

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Sale;
use Illuminate\Http\Request;

class AdminSaleController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Sale::with('user')
            ->when($request->search, function ($q, $search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            })
            ->when($request->type, function ($q, $type) {
                $q->where('type', $type);
            })
            ->when($request->date_from, function ($q, $dateFrom) {
                $q->whereDate('date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($q, $dateTo) {
                $q->whereDate('date', '<=', $dateTo);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');

        $sales = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($sales);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreSaleRequest $request)
    {
        $sale = Sale::create($request->validated());
        $sale->load('user');

        return $this->successResponse($sale, 'Sale created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load('user');
        return $this->successResponse($sale);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        $sale->update($request->validated());
        $sale->load('user');

        return $this->successResponse($sale, 'Sale updated successfully');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Sale $sale)
    {
        $sale->delete();
        return $this->successResponse(null, 'Sale deleted successfully');
    }

    /**
     * Get sales reports
     */
    public function reports(Request $request)
    {
        $reports = Sale::selectRaw('
            DATE(date) as date,
            type,
            SUM(amount) as total_amount,
            COUNT(*) as total_sales
        ')
        ->groupBy('date', 'type')
        ->orderBy('date', 'desc')
        ->get();

        return $this->successResponse($reports);
    }
}
```

## Content Controller Template

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Models\ContentPost;
use Illuminate\Http\Request;

class AdminContentController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ContentPost::with('user')
            ->when($request->search, function ($q, $search) {
                $q->where('platform', 'like', "%{$search}%");
            })
            ->when($request->platform, function ($q, $platform) {
                $q->where('platform', $platform);
            })
            ->when($request->date_from, function ($q, $dateFrom) {
                $q->whereDate('date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($q, $dateTo) {
                $q->whereDate('date', '<=', $dateTo);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');

        $content = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($content);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreContentRequest $request)
    {
        $content = ContentPost::create($request->validated());
        $content->load('user');

        return $this->successResponse($content, 'Content post created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ContentPost $content)
    {
        $content->load('user');
        return $this->successResponse($content);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateContentRequest $request, ContentPost $content)
    {
        $content->update($request->validated());
        $content->load('user');

        return $this->successResponse($content, 'Content post updated successfully');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(ContentPost $content)
    {
        $content->delete();
        return $this->successResponse(null, 'Content post deleted successfully');
    }

    /**
     * Get content analytics
     */
    public function analytics(Request $request)
    {
        $analytics = ContentPost::selectRaw('
            platform,
            SUM(post_count) as total_posts,
            AVG(JSON_EXTRACT(engagement_metrics, "$.likes")) as avg_likes,
            AVG(JSON_EXTRACT(engagement_metrics, "$.shares")) as avg_shares,
            AVG(JSON_EXTRACT(engagement_metrics, "$.comments")) as avg_comments
        ')
        ->groupBy('platform')
        ->get();

        return $this->successResponse($analytics);
    }
}
```

## Expense Controller Template

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\Request;

class AdminExpenseController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expense::with('user')
            ->when($request->search, function ($q, $search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            })
            ->when($request->category, function ($q, $category) {
                $q->where('category', $category);
            })
            ->when($request->date_from, function ($q, $dateFrom) {
                $q->whereDate('date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($q, $dateTo) {
                $q->whereDate('date', '<=', $dateTo);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');

        $expenses = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($expenses);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreExpenseRequest $request)
    {
        $expense = Expense::create($request->validated());
        $expense->load('user');

        return $this->successResponse($expense, 'Expense created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        $expense->load('user');
        return $this->successResponse($expense);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());
        $expense->load('user');

        return $this->successResponse($expense, 'Expense updated successfully');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return $this->successResponse(null, 'Expense deleted successfully');
    }

    /**
     * Get expense categories
     */
    public function categories()
    {
        $categories = Expense::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return $this->successResponse($categories);
    }
}
```

## Goal Controller Template

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Models\Goal;
use Illuminate\Http\Request;

class AdminGoalController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Goal::with('user')
            ->when($request->search, function ($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->quarter, function ($q, $quarter) {
                $q->where('quarter', $quarter);
            })
            ->when($request->year, function ($q, $year) {
                $q->where('year', $year);
            })
            ->when($request->status, function ($q, $status) {
                if ($status === 'completed') {
                    $q->whereColumn('current_value', '>=', 'target_value');
                } elseif ($status === 'in_progress') {
                    $q->whereColumn('current_value', '<', 'target_value')
                      ->where('deadline', '>', now());
                } elseif ($status === 'overdue') {
                    $q->whereColumn('current_value', '<', 'target_value')
                      ->where('deadline', '<', now());
                }
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');

        $goals = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($goals);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreGoalRequest $request)
    {
        $goal = Goal::create($request->validated());
        $goal->load('user');

        return $this->successResponse($goal, 'Goal created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Goal $goal)
    {
        $goal->load('user');
        return $this->successResponse($goal);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateGoalRequest $request, Goal $goal)
    {
        $goal->update($request->validated());
        $goal->load('user');

        return $this->successResponse($goal, 'Goal updated successfully');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Goal $goal)
    {
        $goal->delete();
        return $this->successResponse(null, 'Goal deleted successfully');
    }

    /**
     * Update goal progress
     */
    public function updateProgress(Request $request, Goal $goal)
    {
        $request->validate([
            'current_value' => 'required|numeric|min:0'
        ]);

        $goal->update(['current_value' => $request->current_value]);

        return $this->successResponse($goal, 'Goal progress updated successfully');
    }

    /**
     * Get goal analytics
     */
    public function analytics(Request $request)
    {
        $analytics = Goal::selectRaw('
            quarter,
            year,
            COUNT(*) as total_goals,
            SUM(CASE WHEN current_value >= target_value THEN 1 ELSE 0 END) as completed_goals,
            AVG((current_value / target_value) * 100) as avg_progress
        ')
        ->groupBy('quarter', 'year')
        ->orderBy('year', 'desc')
        ->orderBy('quarter', 'desc')
        ->get();

        return $this->successResponse($analytics);
    }
}
```

## Task Controller Template

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class AdminTaskController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::with('assignedUser')
            ->when($request->search, function ($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->priority, function ($q, $priority) {
                $q->where('priority', $priority);
            })
            ->when($request->assigned_to, function ($q, $assignedTo) {
                $q->where('assigned_to', $assignedTo);
            })
            ->when($request->due_date_from, function ($q, $dateFrom) {
                $q->whereDate('due_date', '>=', $dateFrom);
            })
            ->when($request->due_date_to, function ($q, $dateTo) {
                $q->whereDate('due_date', '<=', $dateTo);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc');

        $tasks = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($tasks);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreTaskRequest $request)
    {
        $task = Task::create($request->validated());
        $task->load('assignedUser');

        return $this->successResponse($task, 'Task created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $task->load('assignedUser');
        return $this->successResponse($task);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());
        $task->load('assignedUser');

        return $this->successResponse($task, 'Task updated successfully');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return $this->successResponse(null, 'Task deleted successfully');
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled'
        ]);

        $task->update(['status' => $request->status]);

        if ($request->status === 'completed') {
            $task->update(['completed_at' => now()]);
        } else {
            $task->update(['completed_at' => null]);
        }

        return $this->successResponse($task, 'Task status updated successfully');
    }

    /**
     * Get task reports
     */
    public function reports(Request $request)
    {
        $reports = Task::selectRaw('
            DATE(created_at) as date,
            status,
            priority,
            COUNT(*) as total_tasks
        ')
        ->groupBy('date', 'status', 'priority')
        ->orderBy('date', 'desc')
        ->get();

        return $this->successResponse($reports);
    }
}
```

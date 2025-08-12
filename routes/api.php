<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ContentPostController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Sales routes
    Route::prefix('sales')->group(function () {
        Route::get('/', [SalesController::class, 'index']);
        Route::post('/', [SalesController::class, 'store']);
        Route::get('/statistics', [SalesController::class, 'statistics']);
        Route::get('/{sale}', [SalesController::class, 'show']);
        Route::put('/{sale}', [SalesController::class, 'update']);
        Route::delete('/{sale}', [SalesController::class, 'destroy']);
    });

    // Content Posts routes
    Route::prefix('content-posts')->group(function () {
        Route::get('/', [ContentPostController::class, 'index']);
        Route::post('/', [ContentPostController::class, 'store']);
        Route::get('/statistics', [ContentPostController::class, 'statistics']);
        Route::get('/{contentPost}', [ContentPostController::class, 'show']);
        Route::put('/{contentPost}', [ContentPostController::class, 'update']);
        Route::delete('/{contentPost}', [ContentPostController::class, 'destroy']);
    });

    // Expenses routes
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
        Route::get('/statistics', [ExpenseController::class, 'statistics']);
        Route::get('/{expense}', [ExpenseController::class, 'show']);
        Route::put('/{expense}', [ExpenseController::class, 'update']);
        Route::delete('/{expense}', [ExpenseController::class, 'destroy']);
    });

    // Goals routes
    Route::prefix('goals')->group(function () {
        Route::get('/', [GoalController::class, 'index']);
        Route::post('/', [GoalController::class, 'store']);
        Route::get('/statistics', [GoalController::class, 'statistics']);
        Route::get('/{goal}', [GoalController::class, 'show']);
        Route::put('/{goal}', [GoalController::class, 'update']);
        Route::put('/{goal}/progress', [GoalController::class, 'updateProgress']);
        Route::delete('/{goal}', [GoalController::class, 'destroy']);
    });

    // Tasks routes
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::get('/my-tasks', [TaskController::class, 'myTasks']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/statistics', [TaskController::class, 'statistics']);
        Route::get('/{task}', [TaskController::class, 'show']);
        Route::put('/{task}', [TaskController::class, 'update']);
        Route::put('/{task}/status', [TaskController::class, 'updateStatus']);
        Route::delete('/{task}', [TaskController::class, 'destroy']);
    });

    // Users routes for dropdowns
    Route::get('/users', function (Request $request) {
        $query = \App\Models\User::query();
        
        if ($request->has('role')) {
            $role = $request->get('role');
            $query->role($role);
        }
        
        return response()->json([
            'data' => $query->get(['id', 'name', 'email'])
        ]);
    });

    // Categories routes for dropdowns
    Route::get('/categories', function (Request $request) {
        $query = \App\Models\Category::query();
        
        if ($request->has('type')) {
            $type = $request->get('type');
            $query->where('type', $type);
        }
        
        return response()->json([
            'data' => $query->get(['id', 'name', 'type'])
        ]);
    });
});
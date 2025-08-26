<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ContentPostController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

// Dashboard API routes
Route::middleware(['auth', 'verified'])->prefix('api/dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.api.index');
    Route::get('/daily-summary', [DashboardController::class, 'getDailySummary'])->name('dashboard.api.daily-summary');
    Route::get('/activity-distribution', [DashboardController::class, 'getActivityDistribution'])->name('dashboard.api.activity-distribution');
    Route::get('/sales-metrics', [DashboardController::class, 'getSalesMetrics'])->name('dashboard.api.sales-metrics');
    Route::get('/expenses', [DashboardController::class, 'getExpenses'])->name('dashboard.api.expenses');
    Route::get('/content-stats', [DashboardController::class, 'getContentStats'])->name('dashboard.api.content-stats');
    Route::get('/quarterly-goals', [DashboardController::class, 'getQuarterlyGoals'])->name('dashboard.api.quarterly-goals');
});

// No API routes - using pure Inertia routing

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Sales Routes
Route::middleware(['auth', 'verified'])->prefix('sales')->group(function () {
    Route::get('/', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/create', [SalesController::class, 'create'])->name('sales.create');
    Route::post('/', [SalesController::class, 'store'])->name('sales.store');
    Route::get('/{sale}', [SalesController::class, 'show'])->name('sales.show');
    Route::get('/{sale}/edit', [SalesController::class, 'edit'])->name('sales.edit');
    Route::put('/{sale}', [SalesController::class, 'update'])->name('sales.update');
    Route::delete('/{sale}', [SalesController::class, 'destroy'])->name('sales.destroy');
});

// Content Routes
Route::middleware(['auth', 'verified'])->prefix('content')->group(function () {
    Route::get('/', [ContentPostController::class, 'index'])->name('content.index');
    Route::get('/create', [ContentPostController::class, 'create'])->name('content.create');
    Route::post('/', [ContentPostController::class, 'store'])->name('content.store');
    Route::get('/{contentPost}', [ContentPostController::class, 'show'])->name('content.show');
    Route::get('/{contentPost}/edit', [ContentPostController::class, 'edit'])->name('content.edit');
    Route::put('/{contentPost}', [ContentPostController::class, 'update'])->name('content.update');
    Route::delete('/{contentPost}', [ContentPostController::class, 'destroy'])->name('content.destroy');
});

// Expenses Routes
Route::middleware(['auth', 'verified'])->prefix('expenses')->group(function () {
    Route::get('/', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
});

// Goals Routes
Route::middleware(['auth', 'verified'])->prefix('goals')->group(function () {
    Route::get('/', [GoalController::class, 'index'])->name('goals.index');
    Route::get('/create', [GoalController::class, 'create'])->name('goals.create');
    Route::post('/', [GoalController::class, 'store'])->name('goals.store');
    Route::get('/{goal}', [GoalController::class, 'show'])->name('goals.show');
    Route::get('/{goal}/edit', [GoalController::class, 'edit'])->name('goals.edit');
    Route::put('/{goal}', [GoalController::class, 'update'])->name('goals.update');
    Route::delete('/{goal}', [GoalController::class, 'destroy'])->name('goals.destroy');
});

// Tasks Routes
Route::middleware(['auth', 'verified'])->prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('tasks.my-tasks');
    Route::get('/statistics', [TaskController::class, 'statistics'])->name('tasks.statistics');
    Route::get('/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::put('/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::delete('/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
});

// User Management Routes (Admin only)
Route::middleware(['auth', 'verified', 'admin'])->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users.index');
    Route::get('/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

// Admin Routes (Admin only)
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    // Use DashboardController so the Admin dashboard page receives the same
    // Inertia props ('dashboardData' and 'lastUpdated') as the main dashboard.
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');
});

// Manager Routes (Manager and Admin)
Route::middleware(['auth', 'verified', 'manager'])->prefix('manager')->group(function () {
    // Use DashboardController so the Manager dashboard page receives the
    // same Inertia props ('dashboardData' and 'lastUpdated') as the main dashboard.
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('manager.dashboard');
});

// Virtual Assistant Routes (All authenticated users)
Route::middleware(['auth', 'verified', 'va'])->prefix('va')->group(function () {
    // Use DashboardController so the VA dashboard page receives the
    // same Inertia props ('dashboardData' and 'lastUpdated') as the main dashboard.
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('va.dashboard');
});

require __DIR__ . '/auth.php';

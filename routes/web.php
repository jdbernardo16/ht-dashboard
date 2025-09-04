<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ContentPostController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
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

// Notification API routes
Route::middleware(['auth', 'verified'])->prefix('api/notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.api.index');
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.api.unread-count');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.api.mark-all-read');
    Route::get('/{notification}', [NotificationController::class, 'show'])->name('notifications.api.show');
    Route::put('/{notification}', [NotificationController::class, 'update'])->name('notifications.api.update');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('notifications.api.destroy');
});

// Notifications Page Route
Route::middleware(['auth', 'verified'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'indexPage'])->name('notifications.index');
});

// No API routes - using pure Inertia routing

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Sales Routes
Route::middleware(['auth', 'verified'])->prefix('sales')->group(function () {
    Route::get('/', [SalesController::class, 'index'])->name('sales.web.index');
    Route::get('/create', [SalesController::class, 'create'])->name('sales.web.create');
    Route::post('/', [SalesController::class, 'store'])->name('sales.web.store');
    Route::get('/{sale}', [SalesController::class, 'show'])->name('sales.web.show');
    Route::get('/{sale}/edit', [SalesController::class, 'edit'])->name('sales.web.edit');
    Route::put('/{sale}', [SalesController::class, 'update'])->name('sales.web.update');
    Route::delete('/{sale}', [SalesController::class, 'destroy'])->name('sales.web.destroy');
});

// Content Routes
Route::middleware(['auth', 'verified'])->prefix('content')->group(function () {
    Route::get('/', [ContentPostController::class, 'index'])->name('content.web.index');
    Route::get('/create', [ContentPostController::class, 'create'])->name('content.web.create');
    Route::post('/', [ContentPostController::class, 'store'])->name('content.web.store');
    Route::get('/{contentPost}', [ContentPostController::class, 'show'])->name('content.web.show');
    Route::get('/{contentPost}/edit', [ContentPostController::class, 'edit'])->name('content.web.edit');
    Route::put('/{contentPost}', [ContentPostController::class, 'update'])->name('content.web.update');
    Route::delete('/{contentPost}', [ContentPostController::class, 'destroy'])->name('content.web.destroy');
});

// Expenses Routes
Route::middleware(['auth', 'verified'])->prefix('expenses')->group(function () {
    Route::get('/', [ExpenseController::class, 'index'])->name('expenses.web.index');
    Route::get('/create', [ExpenseController::class, 'create'])->name('expenses.web.create');
    Route::post('/', [ExpenseController::class, 'store'])->name('expenses.web.store');
    Route::get('/{expense}', [ExpenseController::class, 'show'])->name('expenses.web.show');
    Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.web.edit');
    Route::put('/{expense}', [ExpenseController::class, 'update'])->name('expenses.web.update');
    Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.web.destroy');
});

// Goals Routes
Route::middleware(['auth', 'verified'])->prefix('goals')->group(function () {
    Route::get('/', [GoalController::class, 'index'])->name('goals.web.index');
    Route::get('/create', [GoalController::class, 'create'])->name('goals.web.create');
    Route::post('/', [GoalController::class, 'store'])->name('goals.web.store');
    Route::get('/{goal}', [GoalController::class, 'show'])->name('goals.web.show');
    Route::get('/{goal}/edit', [GoalController::class, 'edit'])->name('goals.web.edit');
    Route::put('/{goal}', [GoalController::class, 'update'])->name('goals.web.update');
    Route::delete('/{goal}', [GoalController::class, 'destroy'])->name('goals.web.destroy');
});

// Tasks Routes
Route::middleware(['auth', 'verified'])->prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('tasks.web.index');
    Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('tasks.web.my-tasks');
    Route::get('/statistics', [TaskController::class, 'statistics'])->name('tasks.web.statistics');
    Route::get('/create', [TaskController::class, 'create'])->name('tasks.web.create');
    Route::post('/', [TaskController::class, 'store'])->name('tasks.web.store');
    Route::get('/{task}', [TaskController::class, 'show'])->name('tasks.web.show');
    Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('tasks.web.edit');
    Route::put('/{task}', [TaskController::class, 'update'])->name('tasks.web.update');
    Route::put('/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.web.update-status');
    Route::delete('/{task}', [TaskController::class, 'destroy'])->name('tasks.web.destroy');

    // Media routes
    Route::delete('/{task}/media/{media}', [TaskController::class, 'destroyMedia'])->name('tasks.web.media.destroy');
});

// User Management Routes (Admin only)
Route::middleware(['auth', 'verified', 'admin'])->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users.web.index');
    Route::get('/create', [UserController::class, 'create'])->name('users.web.create');
    Route::post('/', [UserController::class, 'store'])->name('users.web.store');
    Route::get('/{user}', [UserController::class, 'show'])->name('users.web.show');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.web.edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('users.web.update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.web.destroy');
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

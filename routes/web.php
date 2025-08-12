<?php

use App\Http\Controllers\ProfileController;
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
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// API Routes
Route::middleware(['auth', 'verified'])->prefix('api')->group(function () {
    Route::get('/tasks', [\App\Http\Controllers\TaskController::class, 'apiIndex']);
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index']);
    Route::get('/goals', [\App\Http\Controllers\GoalController::class, 'index']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Sales Routes
Route::middleware(['auth', 'verified'])->prefix('sales')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Sales/Index');
    })->name('sales.index');

    Route::get('/create', function () {
        return Inertia::render('Sales/Create');
    })->name('sales.create');

    Route::get('/{id}/edit', function ($id) {
        return Inertia::render('Sales/Edit', ['id' => $id]);
    })->name('sales.edit');

    Route::get('/{id}', function ($id) {
        return Inertia::render('Sales/Show', ['id' => $id]);
    })->name('sales.show');
});

// Content Routes
Route::middleware(['auth', 'verified'])->prefix('content')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Content/Index');
    })->name('content.index');

    Route::get('/create', function () {
        return Inertia::render('Content/Create');
    })->name('content.create');

    Route::get('/{id}/edit', function ($id) {
        return Inertia::render('Content/Edit', ['id' => $id]);
    })->name('content.edit');

    Route::get('/{id}', function ($id) {
        return Inertia::render('Content/Show', ['id' => $id]);
    })->name('content.show');
});

// Expenses Routes
Route::middleware(['auth', 'verified'])->prefix('expenses')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Expenses/Index');
    })->name('expenses.index');

    Route::get('/create', function () {
        return Inertia::render('Expenses/Create');
    })->name('expenses.create');

    Route::get('/{id}/edit', function ($id) {
        return Inertia::render('Expenses/Edit', ['id' => $id]);
    })->name('expenses.edit');

    Route::get('/{id}', function ($id) {
        return Inertia::render('Expenses/Show', ['id' => $id]);
    })->name('expenses.show');
});

// Goals Routes
Route::middleware(['auth', 'verified'])->prefix('goals')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Goals/Index');
    })->name('goals.index');

    Route::get('/create', function () {
        return Inertia::render('Goals/Create');
    })->name('goals.create');

    Route::get('/{id}/edit', function ($id) {
        return Inertia::render('Goals/Edit', ['id' => $id]);
    })->name('goals.edit');

    Route::get('/{id}', function ($id) {
        return Inertia::render('Goals/Show', ['id' => $id]);
    })->name('goals.show');
});

// Tasks Routes
Route::middleware(['auth', 'verified'])->prefix('tasks')->group(function () {
    Route::get('/', [\App\Http\Controllers\TaskController::class, 'index'])->name('tasks.index');
    Route::get('/my-tasks', [\App\Http\Controllers\TaskController::class, 'myTasks'])->name('tasks.my-tasks');
    Route::get('/statistics', [\App\Http\Controllers\TaskController::class, 'statistics'])->name('tasks.statistics');
    Route::post('/', [\App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    Route::get('/create', function () {
        return Inertia::render('Tasks/Create');
    })->name('tasks.create');
    Route::get('/{task}', [\App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');
    Route::get('/{task}/edit', function ($task) {
        return Inertia::render('Tasks/Edit', ['id' => $task]);
    })->name('tasks.edit');
    Route::put('/{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
    Route::put('/{task}/status', [\App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::delete('/{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');
});

// Admin Routes (Admin only)
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');
});

// Manager Routes (Manager and Admin)
Route::middleware(['auth', 'verified', 'manager'])->prefix('manager')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Manager/Dashboard');
    })->name('manager.dashboard');
});

// Virtual Assistant Routes (All authenticated users)
Route::middleware(['auth', 'verified', 'va'])->prefix('va')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('VA/Dashboard');
    })->name('va.dashboard');
});

require __DIR__.'/auth.php';

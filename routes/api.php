<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API routes for your application
Route::middleware('auth:sanctum')->group(function () {
    // Sales API routes
    Route::apiResource('sales', \App\Http\Controllers\SalesController::class);

    // Tasks API routes
    Route::apiResource('tasks', \App\Http\Controllers\TaskController::class);

    // Expenses API routes
    Route::apiResource('expenses', \App\Http\Controllers\ExpenseController::class);

    // Goals API routes
    Route::apiResource('goals', \App\Http\Controllers\GoalController::class);

    // Content Posts API routes
    Route::apiResource('content-posts', \App\Http\Controllers\ContentPostController::class);

    // Users API routes
    Route::apiResource('users', \App\Http\Controllers\UserController::class);

    // Clients API routes
    Route::apiResource('clients', \App\Http\Controllers\ClientController::class);

    // Categories API routes
    Route::apiResource('categories', \App\Http\Controllers\CategoryController::class);

    // Client search for autocomplete functionality
    Route::get('/clients/search', [\App\Http\Controllers\ClientController::class, 'search'])->name('clients.search');

    // Dashboard API routes
    Route::get('/dashboard/data', [\App\Http\Controllers\DashboardController::class, 'getDashboardData'])->name('dashboard.data');
    Route::get('/dashboard/summary', [\App\Http\Controllers\DashboardController::class, 'getSummary'])->name('dashboard.summary');
    Route::get('/dashboard/sales', [\App\Http\Controllers\DashboardController::class, 'getSalesMetricsData'])->name('dashboard.sales');
    Route::get('/dashboard/expenses', [\App\Http\Controllers\DashboardController::class, 'getExpensesData'])->name('dashboard.expenses');
    Route::get('/dashboard/content', [\App\Http\Controllers\DashboardController::class, 'getContentStatsData'])->name('dashboard.content');
});

// File upload API routes
Route::prefix('upload')->name('upload.')->group(function () {
    Route::get('/config', [\App\Http\Controllers\FileUploadController::class, 'config'])->name('config');
    Route::post('/', [\App\Http\Controllers\FileUploadController::class, 'upload'])->name('single');
    Route::post('/multiple', [\App\Http\Controllers\FileUploadController::class, 'uploadMultiple'])->name('multiple');
    Route::delete('/{file}', [\App\Http\Controllers\FileUploadController::class, 'destroy'])->name('destroy');
});

// Optional: Admin routes with authentication
Route::middleware(['auth:sanctum'])->prefix('admin/upload')->name('admin.upload.')->group(function () {
    Route::post('/', [\App\Http\Controllers\FileUploadController::class, 'upload'])->name('single');
    Route::post('/multiple', [\App\Http\Controllers\FileUploadController::class, 'uploadMultiple'])->name('multiple');
    Route::delete('/{file}', [\App\Http\Controllers\FileUploadController::class, 'destroy'])->name('destroy');
});

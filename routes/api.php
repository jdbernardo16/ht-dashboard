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
});

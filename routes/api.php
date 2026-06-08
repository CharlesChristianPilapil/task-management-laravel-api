<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InternalNotificationController;
use App\Http\Controllers\SchedulerController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('internal')->group(function () {
    Route::prefix('internal')->group(function () {
        Route::get('/notifications/{task}/{user}', [InternalNotificationController::class, 'show']);
        Route::get('/scheduler/daily-digest', [SchedulerController::class, 'dailyDigest']);
        Route::get('/scheduler/deadline-reminders', [SchedulerController::class, 'deadlineReminders']);
        Route::get('/scheduler/stale-cancelled-tasks', [SchedulerController::class, 'staleCancelledTasks']);
    });

    Route::delete('/tasks/{task}/archive', [SchedulerController::class, 'archive']);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:api', 'active'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware(['auth:api', 'active'])->group(function () {

    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::patch('/users/{user}', [UserController::class, 'update']);
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus']);
        Route::get('/teams', [TeamController::class, 'index']);
        Route::post('/teams', [TeamController::class, 'store']);
    });

    Route::get('/teams/{team}', [TeamController::class, 'show']);

    Route::prefix('teams/{team}')->group(function () {
        Route::post('/members', [TeamController::class, 'addMember']);
        Route::delete('/members/{user}', [TeamController::class, 'removeMember']);
        Route::get('/tasks', [TaskController::class, 'index']);
        Route::post('/tasks', [TaskController::class, 'store']);
    });

    Route::get('/tasks/mine', [TaskController::class, 'mine']);

    Route::prefix('tasks/{task}')->group(function () {
        Route::get('/', [TaskController::class, 'show']);
        Route::patch('/', [TaskController::class, 'update']);
        Route::delete('/', [TaskController::class, 'destroy']);
        Route::patch('/status', [TaskController::class, 'updateStatus']);
    });
});

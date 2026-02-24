<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMemberController;

// Public
Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);

// Protected routes - require authentication and role checks
Route::middleware('check.role')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Users
    Route::apiResource('users',         UserController::class);
    Route::post('users/{user}/avatar', [UserController::class, 'updateAvatar']);
    Route::get('users/{user}/tasks',   [UserController::class, 'tasks']);

    // Tasks
    Route::apiResource('tasks',       TaskController::class);
    Route::get('tasks/{task}/user',  [TaskController::class, 'user']);
    
    // Teams
    Route::get('teams',                [TeamController::class, 'index']);
    Route::get('teams/{team}',         [TeamController::class, 'show']);
    Route::post('teams/{team}/avatar', [TeamController::class, 'updateAvatar']);
    Route::post('teams',               [TeamController::class, 'store'])  ->middleware('check.role:Admin');
    Route::patch('teams/{team}',       [TeamController::class, 'update']) ->middleware('check.role:Admin');
    Route::delete('teams/{team}',      [TeamController::class, 'destroy'])->middleware('check.role:Admin');

    Route::post('teams/{team}/members',            [TeamMemberController::class, 'store'])  ->middleware('check.team.leader:team');
    Route::delete('teams/{team}/members/{userId}', [TeamMemberController::class, 'destroy'])->middleware('check.team.leader:team');
    
    // Restore routes for soft-deleted records
    Route::post('users/{id}/restore', [UserController::class, 'restore'])->middleware('check.role:Admin');
    Route::post('tasks/{id}/restore', [TaskController::class, 'restore'])->middleware('check.role:Admin');
    Route::post('teams/{id}/restore', [TeamController::class, 'restore'])->middleware('check.role:Admin');

    // Teams Export/Import
    Route::get('export/teams',  [TeamController::class, 'export']);
    Route::post('import/teams', [TeamController::class, 'import']);
});


<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;

// Public: registration and login
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes - require authentication and role checks
Route::middleware('check.role')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::apiResources([
        'users' => UserController::class,
        'tasks' => TaskController::class,
    
    ]);
    
    Route::apiResource('teams', TeamController::class)
        ->middleware([
            'store'   => 'check.role:Admin',
            'update'  => 'check.role:Admin',
            'destroy' => 'check.role:Admin',
        ]);
 
    Route::get('users/{user}/tasks', [UserController::class, 'tasks']);
    Route::get('tasks/{task}/user', [TaskController::class, 'user']);
});


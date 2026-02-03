<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;

// Public: registration and login
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes - require a valid Sanctum token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::apiResources([
        'users' => UserController::class,
        'tasks' => TaskController::class,
    ]);
 
    Route::get('users/{user}/tasks', [UserController::class, 'tasks']);
    Route::get('tasks/{task}/user', [TaskController::class, 'user']);
});

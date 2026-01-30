<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;

// User registration
Route::apiResource('users', UserController::class);

// Task routes
Route::apiResource('tasks', TaskController::class);

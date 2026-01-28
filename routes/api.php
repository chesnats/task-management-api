<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// User registration
Route::apiResource('users', UserController::class)->except(['store']);
Route::post('register', [AuthController::class, 'register']);


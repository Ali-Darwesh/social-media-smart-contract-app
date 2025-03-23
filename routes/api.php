<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\UserAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/register', [UserAuthController::class, 'register']);
    Route::post('/login', [UserAuthController::class, 'login']);
});


Route::middleware('auth:api')->prefix('auth')->group(function () {
    Route::post('/logout', [UserAuthController::class, 'logout']);
    Route::post('/refresh', [UserAuthController::class, 'refresh']);
    Route::get('/user', [UserAuthController::class, 'user']);
});
Route::prefix('auth/admin')->group(function () {
    Route::post('/register', [AdminAuthController::class, 'register']);
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::post('/refresh', [AdminAuthController::class, 'refresh']);
        Route::get('/profile', [AdminAuthController::class, 'admin']);
    });
});
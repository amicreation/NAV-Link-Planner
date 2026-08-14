<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalculationController;
use App\Http\Controllers\ElevationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    // Authentication Routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/password', [AuthController::class, 'changePassword']);

    // Admin Management Routes
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::post('/admin/users', [AdminUserController::class, 'store']);
    Route::get('/admin/users/{user}', [AdminUserController::class, 'show']);
    Route::put('/admin/users/{user}', [AdminUserController::class, 'update']);
    Route::post('/admin/users/{user}/reset-password', [AdminUserController::class, 'resetPassword']);
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy']);
    Route::get('/admin/audit-logs', [AdminUserController::class, 'auditLogs']);

    // Core Engineering & Projects API Routes
    Route::apiResource('projects', ProjectController::class);
    Route::post('/calculate', [CalculationController::class, 'calculate']);
    Route::get('/elevation/lookup', [ElevationController::class, 'lookup']);
    Route::match(['get', 'post'], '/projects/{project}/pdf', [ReportController::class, 'generatePdf']);
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'update']);
});

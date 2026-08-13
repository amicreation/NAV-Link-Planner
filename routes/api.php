<?php

use App\Http\Controllers\CalculationController;
use App\Http\Controllers\ElevationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::apiResource('projects', ProjectController::class);
Route::post('/calculate', [CalculationController::class, 'calculate']);
Route::get('/elevation/lookup', [ElevationController::class, 'lookup']);
Route::match(['get', 'post'], '/projects/{project}/pdf', [ReportController::class, 'generatePdf']);
Route::get('/settings', [SettingController::class, 'index']);
Route::post('/settings', [SettingController::class, 'update']);

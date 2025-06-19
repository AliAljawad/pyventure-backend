<?php

use App\Http\Controllers\AchievementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\SubmissionController;
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-2fa', [AuthController::class, 'verify2FA']);
Route::get('/leaderboard', [LeaderboardController::class, 'index']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::get('/achievements', [AchievementController::class, 'index']);
    Route::get('/user',[AuthController::class,'user']);

    // Levels routes
    Route::get('/levels', [LevelController::class, 'index']);
    Route::get('/levels/{id}', [LevelController::class, 'show']);
    Route::post('/levels', [LevelController::class, 'store']);
    Route::put('/levels/{id}', [LevelController::class, 'update']);
    Route::delete('/levels/{id}', [LevelController::class, 'destroy']);

    // Submissions routes
    Route::get('/submissions', [SubmissionController::class, 'index']);
    Route::post('/submissions', [SubmissionController::class, 'store']);

    // Progress routes
    Route::get('/progress', [ProgressController::class, 'index']);
    Route::post('/progress', [ProgressController::class, 'update']);

    // MISSING: Individual progress route that your frontend might need
    Route::get('/user/progress', [ProgressController::class, 'index']);
    Route::post('/user/progress', [ProgressController::class, 'update']);
});

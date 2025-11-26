<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ScenarioController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\InterventionController;

Route::get('/scenario/{scenario}', [ScenarioController::class, 'show']);
Route::post('/scenario/submit', [ScenarioController::class, 'submit']);
Route::post('/feedback/intervention', [FeedbackController::class, 'store']);
Route::get('/intervention/trigger', [InterventionController::class, 'trigger']);
Route::get('/leaderboard', [LeaderboardController::class, 'getLeaderboard']);
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeaderboardController; // <-- Impor Controller Anda
use App\Http\Controllers\ThresholdController;
use App\Http\Controllers\ScenarioController;
use App\Http\Controllers\SessionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
// Rute untuk API GET Scenarios (Publik untuk tes)
Route::get('/scenarios', [ScenarioController::class, 'index']);

// Rute untuk API 31 (Publik untuk tes)
// API 19 (BARU)
// Nama '{scenario}' harus cocok dengan variabel $scenario di Controller
Route::get('/scenario/{scenario}', [ScenarioController::class, 'show']);
// API 29 - Threshold
Route::get('/threshold', [ThresholdController::class, 'getThresholds']);
//API 30 - Leaderboard
Route::get('/leaderboard', [LeaderboardController::class, 'getLeaderboard']);

Route::get('/sessions/completed', [SessionController::class, 'getCompletedSessions']);
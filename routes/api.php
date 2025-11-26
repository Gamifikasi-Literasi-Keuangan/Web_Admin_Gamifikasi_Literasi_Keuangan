<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ScenarioController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\InterventionController;

/*
|--------------------------------------------------------------------------
| API Routes - Sesuai Spesifikasi V3
|--------------------------------------------------------------------------
*/

// ========================================
// TILE / SCENARIO / CARD APIs
// ========================================
// API 19: GET /scenario/{scenario_id} - Mengambil data skenario
Route::get('/scenario/{scenario}', [ScenarioController::class, 'show']);

// API 20: POST /scenario/submit - Menyimpan pilihan player terhadap skenario
Route::post('/scenario/submit', [ScenarioController::class, 'submit']);


// ========================================
// FEEDBACK & INTERVENTION APIs
// ========================================
// API 28: POST /feedback/intervention - Menyimpan hasil intervensi/perilaku player
Route::post('/feedback/intervention', [FeedbackController::class, 'store']);

// GET /intervention/trigger - Mengambil pesan intervensi berdasarkan level risiko
Route::get('/intervention/trigger', [InterventionController::class, 'trigger']);


// ========================================
// LEADERBOARD & PERFORMANCE APIs
// ========================================
// API 30: GET /leaderboard - Menampilkan ranking pemain
Route::get('/leaderboard', [LeaderboardController::class, 'getLeaderboard']);


// ========================================
// AUTHENTICATION APIs (Belum Diimplementasi)
// ========================================
// TODO: API 1: POST /auth/google - Login dengan Google OAuth
// TODO: API 2: POST /auth/refresh - Refresh JWT token
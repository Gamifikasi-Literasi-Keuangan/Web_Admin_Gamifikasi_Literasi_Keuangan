<?php

use App\Http\Controllers\LeaderboardController;

// ... (Rute lain)

// Rute untuk API 31 (tanpa auth middleware)
// Ini membuatnya menjadi Rute Publik
Route::get('/leaderboard', [LeaderboardController::class, 'getLeaderboard']);
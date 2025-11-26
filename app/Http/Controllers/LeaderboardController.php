<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParticipatesIn; // <-- Impor Model Anda
use Illuminate\Support\Facades\Validator; // <-- Impor Validator

class LeaderboardController extends Controller
{
    /**
     * API 30: GET /leaderboard
     * Menampilkan ranking pemain setelah game berakhir
     * 
     * Catatan: Dalam implementasi production, session_id seharusnya diambil dari:
     * - Session context yang baru selesai
     * - Auth token player yang aktif
     * 
     * Untuk sementara testing, ambil session terakhir yang selesai
     */
    public function getLeaderboard(Request $request)
    {
        // Ambil session terakhir yang selesai (status = 'completed')
        $latestSession = \App\Models\Session::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestSession) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada session yang selesai'
            ], 404);
        }

        $sessionId = $latestSession->sessionId;

        // Ambil ranking dari session tersebut
        $rankings = ParticipatesIn::with('player:PlayerId,name')
            ->where('sessionId', $sessionId)
            ->orderBy('score', 'DESC')
            ->get();

        // Format Response
        $formattedRankings = $rankings->map(function ($participation, $key) {
            return [
                'player_id' => $participation->player->PlayerId,
                'username' => $participation->player->name,
                'overall' => $participation->score,
                'rank' => $key + 1
            ];
        });

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'rankings' => $formattedRankings,
            'generated_at' => now()->toIso8601String()
        ], 200);
    }
}
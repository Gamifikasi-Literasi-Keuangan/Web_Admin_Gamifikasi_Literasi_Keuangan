<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParticipatesIn; // <-- Impor Model Anda
use Illuminate\Support\Facades\Validator; // <-- Impor Validator

class LeaderboardController extends Controller
{
    /**
     * Ini adalah implementasi API 31,
     * mengikuti sequence diagram (1 query JOIN).
     */
    public function getLeaderboard(Request $request)
    {
        // === Langkah 2: Validasi (Diagram: Validator) ===
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|exists:sessions,sessionId', // Pastikan sesi ada
            'limit'      => 'sometimes|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validated = $validator->validated();
        $sessionId = $validated['session_id'];
        $limit = $request->input('limit', 50); // Default 50

        // === Langkah 4 & 5: Query Inti (Diagram: Service -> Repo -> DB) ===
        // Ini adalah 1-query JOIN yang efisien, persis seperti di diagram.
        $rankings = ParticipatesIn::where('sessionId', $sessionId)
            ->with('player:PlayerId,name') // Ini adalah "JOIN players p ON..."
            ->orderBy('score', 'DESC')     // "ORDER BY score DESC"
            ->limit($limit)
            ->get();

        // === Langkah 6: Format Hasil (Diagram: Service -> Service) ===
        $formattedRankings = $rankings->map(function ($participation, $key) {
            return [
                'player_id' => $participation->player->PlayerId,
                'username'  => $participation->player->name,
                'overall'   => $participation->score,
                'rank'      => $key + 1 // Hitung Peringkat (1, 2, 3...)
            ];
        });

        // === Langkah 7 & 8: Kirim Respons (Diagram: Service -> Controller -> Client) ===
        return response()->json([
            'session_id'   => $sessionId,
            'limit'        => $limit,
            'rankings'     => $formattedRankings,
            'generated_at' => now()->toIso8601String()
        ]);
    }
}
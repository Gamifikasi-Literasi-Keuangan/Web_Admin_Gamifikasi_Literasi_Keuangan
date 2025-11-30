<?php

namespace App\Services;

use App\Models\GameSession;
use App\Models\ParticipatesIn;
use App\Models\BoardTile;
use App\Models\Config;
use App\Models\Player;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SessionService {
    public function getSessionState(string $playerId) {
        $participation = ParticipatesIn::where('playerId', $playerId)
            ->whereHas('session', function ($query) {
                $query->whereIn('status', ['active', 'waiting']);
            })
            ->with('session.participants')
            ->first();

        if(!$participation) {
            $lobby = ParticipatesIn::where('playerId', $playerId)
            ->whereHas('session', fn($q) => $q->where('status', 'waiting'))
            ->first();
            
            if($lobby) {
                return ['error' => 'Game has not started yet. Please use /matchmaking/status'];
            }

            return ['error' => 'Player is not in an active session'];
        }

        $session = $participation->session;
        $gameState = json_decode($session->game_state, true) ?? [];
        $turnPhase = $gameState['turn_phase'] ?? 'waiting';

        $playersData = [];
        $scoresData = [];
        $positionsData = [];

        $tiles = BoardTile::pluck('name', 'position_index');

        foreach ($session->participants as $p) {
            $playersData[] = [
                'player_id' => $p->playerId,
                'username' => $p->player->name ?? 'Unknown',
                'character_id' => $p->player->character_id ?? 1,
                'connected' => $p->connection_status === 'connected',
                'is_ready' => (bool) $p->is_ready
            ];

            $pScore = $gameState['scores'][$p->playerId] ?? [
                "pendapatan" => 0,
                "anggaran" => 0,
                "tabungan" => 0,
                "utang" => 0,
                "investasi" => 0,
                "asuransi" => 0,
                "tujuan_jangka_panjang" => 0,
                "overall" => $p->score
            ];
            $scoresData[] = $pScore;

            $tileName = $tiles[$p->position] ?? 'Start';
            $positionsData[] = [
                'tile_id' => $p->position,
                'tile_name' => $tileName
            ];
        }

        $currentPlayerName = 'None';
        if ($session->current_player_id) {
            $currentPlayer = $session->participants->firstWhere('playerId', $session->current_player_id);
            $currentPlayerName = $currentPlayer ? $currentPlayer->player->name : 'Unknown';
        }

        return [
            "session_id" => $session->sessionId,
            "status" => $session->status,
            "current_turn_player_id" => $session->current_player_id,
            "current_turn_player_name" => $currentPlayerName,
            "turn_phase" => $turnPhase,
            "turn_number" => $session->current_turn,
            "players" => $playersData,
            "scores" => $scoresData,
            "positions" => $positionsData
        ];
    }
}

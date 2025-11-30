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

    public function startTurn(string $playerId)
    {
        $participation = ParticipatesIn::where('playerId', $playerId)
            ->whereHas('session', function ($query) {
                $query->where('status', 'active');
            })
            ->first();

        if (!$participation) {
            return ['error' => 'Player is not in an active session'];
        }

        $session = $participation->session;

        if ($session->current_player_id !== $playerId) {
            return ['error' => 'It is not your turn yet'];
        }

        $gameState = json_decode($session->game_state, true) ?? [];
        $gameState['turn_phase'] = 'waiting';
        
        $session->game_state = json_encode($gameState);
        $session->save();

        return [
            'turn_phase' => 'waiting',
            'turn_number' => $session->current_turn
        ];
    }

    /**
     * Handle rolling the dice for the player's turn.
     */
    public function rollDice(string $playerId)
    {
        $participation = ParticipatesIn::where('playerId', $playerId)
            ->whereHas('session', fn($q) => $q->where('status', 'active'))
            ->first();

        if (!$participation) {
            return ['error' => 'Player is not in an active session'];
        }

        $session = $participation->session;

        if ($session->current_player_id !== $playerId) {
            return ['error' => 'It is not your turn'];
        }

        $gameState = json_decode($session->game_state, true) ?? [];
        $currentPhase = $gameState['turn_phase'] ?? 'waiting';

        if ($currentPhase !== 'waiting') {
            return ['error' => "Cannot roll dice in '$currentPhase' phase. Please wait or check state."];
        }

        $diceValue = rand(1, 6);

        $gameState['turn_phase'] = 'rolling';
        $gameState['last_dice'] = $diceValue;
        
        $session->game_state = json_encode($gameState);
        $session->save();

        return [
            'turn_phase' => 'rolling',
            'dice_value' => $diceValue
        ];
    }

    /**
     * Move the player based on the last rolled dice.
     */
    public function movePlayer(string $playerId)
    {
        return DB::transaction(function () use ($playerId) {
            $participation = ParticipatesIn::where('playerId', $playerId)
                ->whereHas('session', fn($q) => $q->where('status', 'active'))
                ->with('session')
                ->lockForUpdate()
                ->first();

            if (!$participation) {
                return ['error' => 'Player is not in an active session'];
            }

            $session = $participation->session;

            if ($session->current_player_id !== $playerId) {
                return ['error' => 'It is not your turn'];
            }

            $gameState = json_decode($session->game_state, true) ?? [];
            $currentPhase = $gameState['turn_phase'] ?? 'waiting';

            if ($currentPhase !== 'rolling') {
                return ['error' => "Cannot move in '$currentPhase' phase. You need to roll dice first."];
            }

            $diceValue = $gameState['last_dice'] ?? 0;
            if ($diceValue == 0) {
                return ['error' => 'Dice value invalid. Please roll again.'];
            }

            $currentPosition = $participation->position;
            
            $totalTiles = BoardTile::count();
            if ($totalTiles == 0) $totalTiles = 20;

            $newPosition = ($currentPosition + $diceValue) % $totalTiles;

            if ($newPosition < $currentPosition) {
                // Logika "Pass Go" (Dapat uang) bisa ditaruh di sini
                // $participation->score += 200; 
            }

            $participation->position = $newPosition;
            $participation->save();

            $gameState['turn_phase'] = 'moving';
            $session->game_state = json_encode($gameState);
            $session->save();

            return [
                'turn_phase' => 'moving',
                'from_tile' => $currentPosition,
                'to_tile' => $newPosition
            ];
        });
    }
}

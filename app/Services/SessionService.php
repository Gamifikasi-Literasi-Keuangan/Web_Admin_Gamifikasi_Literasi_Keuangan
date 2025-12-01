<?php

namespace App\Services;

use App\Models\Session;
use App\Models\ParticipatesIn;
use App\Models\BoardTile;
use App\Models\Turn;
use App\Models\Telemetry;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SessionService
{
    public function createSession(string $hostPlayerId, array $playerIds, int $maxTurns): array
    {
        $sessionId = Str::uuid()->toString();

        DB::transaction(function () use ($sessionId, $hostPlayerId, $playerIds, $maxTurns) {
            Session::create([
                'session_id' => $sessionId,
                'host_player_id' => $hostPlayerId,
                'status' => 'waiting',
                'turn_index' => 0,
                'max_turns' => $maxTurns,
            ]);

            foreach ($playerIds as $index => $playerId) {
                ParticipatesIn::create([
                    'session_id' => $sessionId,
                    'player_id' => $playerId,
                    'position_index' => 0,
                    'is_host' => ($playerId === $hostPlayerId),
                ]);
            }
        });

        return [
            'session_id' => $sessionId,
            'host_player_id' => $hostPlayerId,
            'player_ids' => $playerIds,
            'max_turns' => $maxTurns,
            'status' => 'waiting',
        ];
    }
    public function startTurn(string $sessionId, string $playerId)
    {
        // Ambil session
        $session = Session::find($sessionId);
        if (!$session) {
            throw new HttpException(404, "Session tidak ditemukan.");
        }

        // Jika turn sebelumnya belum selesai
        if ($session->status === 'turn_started') {
            throw new HttpException(409, "Giliran sedang berjalan.");
        }

        // Validasi player terdaftar di session
        $isParticipant = ParticipatesIn::where('session_id', $sessionId)
            ->where('player_id', $playerId)
            ->exists();

        if (!$isParticipant) {
            throw new HttpException(422, "Pemain tidak terdaftar di dalam session.");
        }
        $lastTurn = Turn::where('session_id', $sessionId)
            ->orderBy('turn_number', 'desc')
            ->first();
        $nextTurnNumber = $lastTurn ? $lastTurn->turn_number + 1 : 1;
        $timestamp = now();
        $turnId = "turn" . str_pad($nextTurnNumber, 3, '0', STR_PAD_LEFT);

        // Simpan turn baru
        $turn = Turn::create([
            'turn_id'      => $turnId,
            'session_id'   => $sessionId,
            'player_id'    => $playerId,
            'turn_number'  => $nextTurnNumber,
            'started_at'   => $timestamp->toDateTimeString(),
        ]);

        // Update session
        $session->current_player_id = $playerId;
        $session->status = 'turn_started';
        $session->save();

        return [
            'turn_id'         => $turnId,
            'session_id'      => $sessionId,
            'player_id'       => $playerId,
            'status'          => 'started',
            'turn_started_at' => $timestamp->toIso8601String(),
        ];
    }

    public function movePlayer(string $sessionId, string $playerId, int $fromTile, int $steps): array
    {
        // Validasi: player harus berada pada session
        $isParticipant = ParticipatesIn::where('session_id', $sessionId)
            ->where('player_id', $playerId)
            ->exists();

        if (!$isParticipant) {
            throw new HttpException(422, "Pemain tidak terdaftar dalam session.");
        }

        // Menghitung posisi baru (board 40 petak)
        $toTile = ($fromTile + $steps) % 40;

        // Cek apakah melewati start (posisi 0)
        $passedStart = ($fromTile + $steps) >= 40;

        // Update posisi di participatesin
        ParticipatesIn::where('session_id', $sessionId)
            ->where('player_id', $playerId)
            ->update(['position_index' => $toTile]);

        // Ambil tile tujuan
        $tile = BoardTile::where('position_index', $toTile)->first();

        if (!$tile) {
            throw new HttpException(404, "Tile pada posisi $toTile tidak ditemukan.");
        }

        // Bentuk data output extra
        $nextAction = null;

        if ($tile->type === "Scenario") {
            $nextAction = [
                "scenario_id" => $tile->linked_content
            ];
        }

        // Ambil session untuk turn data
        $session = Session::find($sessionId);

        return [
            "status" => "success",
            "player_id" => $playerId,
            "session_id" => $sessionId,

            "move_result" => [
                "from_tile" => $fromTile,
                "to_tile" => $toTile,
                "passed_start" => $passedStart,
                "tile_type" => $tile->type
            ],

            "next_action" => $nextAction,

            "turn" => [
                "current_player_id" => $session->current_player_id,
                "is_action_required" => ($tile->type === "Scenario")
            ]
        ];
    }

    public function endTurn(string $sessionId,string $playerId,string $turnId,int $tileId,string $tileType,array $actions,string $endedAt)   {
        if (is_null($tileId)) {
            throw new \Exception("tile_id tidak boleh null saat log telemetry.");
        }

        return DB::transaction(function () use (
            $sessionId, $playerId, $turnId, $tileId, $tileType, $actions, $endedAt
        ) {
            $turn = Turn::findOrFail($turnId);
            $turn->ended_at = date('Y-m-d H:i:s', strtotime($endedAt));
            $turn->save();

            foreach ($actions as $action) {
                Telemetry::create([
                    'session_id' => $sessionId,
                    'player_id' => $playerId,
                    'turn_id' => $turnId,
                    'tile_id' => $tileId,
                    'action' => $action['action'],
                    'details' => json_encode($action, JSON_UNESCAPED_UNICODE),
                ]);
            }

            $session = Session::with('players')->findOrFail($sessionId);
            $players = $session->players;

            $nextIndex = ($session->turn_index + 1) % $players->count();
            $nextPlayer = $players[$nextIndex];

            $session->turn_index = $nextIndex;
            $session->current_player_id = $nextPlayer->player_id;
            $session->save();

            return [
                'turn_id' => $turnId,
                'status' => 'ended',
                'next_player' => $nextPlayer->player_id
            ];
        });
    }

}

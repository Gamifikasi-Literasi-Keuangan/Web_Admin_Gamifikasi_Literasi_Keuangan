<?php

namespace App\Http\Controllers;

use App\Services\SessionService;
use App\Http\Requests\StartTurnRequest;
use App\Http\Requests\MovePlayerRequest;
use App\Http\Requests\EndTurnRequest;
use App\Http\Requests\CreateSessionRequest;
use App\Http\Requests\EndSessionRequest;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    // Suntikkan (inject) Service
    public function __construct(protected SessionService $sessionService)
    {
    }
    public function createSession(CreateSessionRequest $request)
    {
        $data = $request->validated();
        $result = $this->sessionService->createSession(
            $data['host_player_id'], 
            $data['player_id'],
            $data['max_turns']
        );
        return response()->json($result, 201); // 201 Created
    }

    public function startTurn(StartTurnRequest $request)
    {
        $data = $request->validated();
        $turn = $this->sessionService->startTurn(
            $data['session_id'], 
            $data['player_id'],
            $data['turn_number'],
            $data['timestamp']
        );
        return response()->json($turn, 201);
    }

    public function movePlayer(MovePlayerRequest $request)
    {
        $data = $request->validated();
        $result = $this->sessionService->movePlayer(
            $data['session_id'], 
            $data['player_id'],
            $data['from_tile'],
            $data['steps']
        );
        return response()->json($result);
    }

    public function endTurn(EndTurnRequest $request)
    {
        $data = $request->validated();
        $result = $this->sessionService->endTurn(
            $data['session_id'],
            $data['player_id'],
            $data['turn_id'],
            $data['tile_id'],
            $data['tile_type'],
            $data['actions'],
            $data['turn_ended_at']
        );
        return response()->json($result);
    }

    public function endSession(EndSessionRequest $request, $sessionId) // Contoh jika ID dari URL
    {
        $result = $this->sessionService->endSession($sessionId);
        return response()->json($result);
    }
}
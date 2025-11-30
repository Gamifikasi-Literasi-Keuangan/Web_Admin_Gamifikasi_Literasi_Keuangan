<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MatchmakingService;

class MatchmakingController extends Controller
{
    protected $sessionService;

        public function __construct(MatchmakingService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * POST /matchmaking/join
     * Mencari atau membuat sesi permainan.
     */
    public function join(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->player) {
            return response()->json(['error' => 'Player profile not found'], 404);
        }
        $playerId = $user->player->PlayerId;

        try {
            $result = $this->sessionService->joinMatchmaking($playerId);
            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Matchmaking failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function selectCharacter(Request $request) {
        $request->validate([
            'character_id' => 'required|integer'
        ]);
        
        $user = $request->user();
        if (!$user || !$user->player) {
            return response()->json(['error' => 'Player profile not found'], 404);
        }
        try {
        $result = $this->sessionService->updatePlayerCharacter(
            $user->player->PlayerId,
            $request->input('character_id')
        );

        return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function status(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->player) {
            return response()->json(['error' => 'Player profile not found'], 404);
        }

        try {
            $result = $this->sessionService->getMatchmakingStatus($user->player->PlayerId);
            
            // Jika user tidak punya sesi
            if (isset($result['error'])) {
                return response()->json($result, 404);
            }

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function ready(Request $request) {
        $request->validate([
            'is_ready' => 'required|boolean'
        ]);
        $user = $request->user();

        if (!$user || !$user->player) {
            return response()->json(['error' => 'Player profile not found'], 404);
        }

        try {
            $result = $this->sessionService->setPlayerReady(
                $user->player->PlayerId,
                $request->input('is_ready')
            );
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

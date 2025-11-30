<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SessionService;

class SessionController extends Controller
{
    protected $sessionService;
    public function __construct(SessionService $sessionService)
        {
            $this->sessionService = $sessionService;
        }

    public function state(Request $request){
        $user = $request->user();
        if (!$user || !$user->player) {
            return response()->json(['error' => 'Player profile not found'], 404);
        }

        try {
            $result = $this->sessionService->getSessionState($user->player->PlayerId);

            if (isset($result['error'])) {
                if (str_contains($result['error'], 'not started')) {
                    return response()->json($result, 409);
                }
                return response()->json($result, 404);
            }

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

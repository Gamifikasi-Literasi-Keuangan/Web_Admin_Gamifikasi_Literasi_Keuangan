<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PlayerService;

class AuthController extends Controller
{
    protected $playerService;

    public function __construct(PlayerService $playerService)
    {
        $this->playerService = $playerService;
    }

    /**
     * Login dengan Google
     * POST /auth/google
     */
    public function google(Request $request)
    {
        $data = $request->validate([
            'google_id_token' => 'required|string',
            'platform' => 'nullable|string',
            'locale' => 'nullable|string'
        ]);

        try {
            $result = $this->playerService->loginWithGoogle($data);
            
            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication Failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Refresh Token
     * POST /auth/refresh
     */
    public function refresh(Request $request)
    {
        $data = $request->validate([
            'refresh_token' => 'required|string'
        ]);

        try {
            $result = $this->playerService->refreshToken($data['refresh_token']);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid or Expired Refresh Token'
            ], 401);
        }
    }
}

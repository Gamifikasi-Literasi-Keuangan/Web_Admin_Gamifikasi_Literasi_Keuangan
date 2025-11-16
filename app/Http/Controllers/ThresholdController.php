<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ThresholdService; // <-- Impor Service Anda
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // <-- Impor Validator

class ThresholdController extends Controller
{
    protected $thresholdService;

    // Inject Service ke dalam Controller
    public function __construct(ThresholdService $thresholdService)
    {
        $this->thresholdService = $thresholdService;
    }

    /**
     * Ini adalah implementasi API 29 (GET /threshold)
     */
    public function getThresholds(Request $request)
    {
        // === Langkah 2: Validasi (Diagram: Validator) ===
        $validator = Validator::make($request->all(), [
            'player_id' => 'required|string|exists:players,PlayerId'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $playerId = $request->input('player_id');

        // === Langkah 3 & 4: Panggil Service (Diagram: Controller -> Service) ===
        $data = $this->thresholdService->getPlayerThresholds($playerId);

        if (!$data) {
             return response()->json(['message' => 'Player profile not found'], 404);
        }

        // === Langkah 5: Kirim Respons (Diagram: Controller -> Client) ===
        return response()->json($data);
    }
}
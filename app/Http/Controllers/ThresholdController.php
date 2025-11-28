<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ThresholdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ThresholdController extends Controller
{
    protected $thresholdService;

    public function __construct(ThresholdService $thresholdService)
    {
        $this->thresholdService = $thresholdService;
    }

    public function getThresholds(Request $request)
    {
        // ... (Kode lama Anda tetap di sini) ...
        $validator = Validator::make($request->all(), [
            'player_id' => 'required|string|exists:players,PlayerId'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $playerId = $request->input('player_id');
        $data = $this->thresholdService->getPlayerThresholds($playerId);

        if (!$data) {
             return response()->json(['message' => 'Player profile not found'], 404);
        }

        return response()->json($data);
    }

    // --- FUNGSI BARU: IMPLEMENTASI API 30 (POST /threshold/update) ---
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'player_id' => 'required|string|exists:players,PlayerId',
            'adjustments' => 'required|array', // Contoh: {"critical": 0.40}
            'reason'      => 'nullable|string' // <-- TAMBAHAN: Terima input reason
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $this->thresholdService->manualUpdate(
            $request->input('player_id'), 
            $request->input('adjustments'),
            $validated['reason'] ?? null 
        );

        // Kembalikan data terbaru sebagai konfirmasi
        $newData = $this->thresholdService->getPlayerThresholds($request->input('player_id'));
        
        return response()->json($newData);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ScenarioService;

class ScenarioController extends Controller
{
    protected $scenarioService;

    public function __construct(ScenarioService $scenarioService)
    {
        $this->scenarioService = $scenarioService;
    }

    /**
     * Mengambil detail satu skenario lengkap dengan daftar opsi
     * dan menambahkan flag apakah pemain sedang memicu intervensi.
     */
    public function show(Request $request, $scenarioId)
    {
        // 1. Ambil User dari Token
        $user = $request->user();
        if (!$user || !$user->player) {
            return response()->json(['error' => 'Player profile not found'], 404);
        }

        try {
            // 2. Panggil Service
            $result = $this->scenarioService->getScenarioDetail(
                $user->player->PlayerId, 
                $scenarioId
            );

            // Handle jika tidak ketemu
            if (isset($result['error'])) {
                return response()->json($result, 404);
            }

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Memproses jawaban skenario dari pemain,
     * memperbarui skor lifetime berdasarkan opsi yang dipilih,
     * dan mencatat keputusan untuk analisis lebih lanjut.
     */
    public function submit(Request $request)
    {
        $request->validate([
            'scenario_id' => 'required|string|exists:scenarios,id',
            'selected_option' => 'required|string',
            'decision_time_seconds' => 'numeric'
        ]);

        $user = $request->user();
        if (!$user || !$user->player) {
            return response()->json(['error' => 'Player profile not found'], 404);
        }

        try {
            $result = $this->scenarioService->submitAnswer(
                $user->player->PlayerId,
                $request->all()
            );

            if (isset($result['error'])) {
                return response()->json($result, 400);
            }

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
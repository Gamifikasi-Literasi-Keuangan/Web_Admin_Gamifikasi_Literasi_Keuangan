<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Scenario;
use App\Services\ScenarioService; // Service logika utama
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScenarioController extends Controller
{
    protected $service;

    // Inject ScenarioService ke dalam Controller
    public function __construct(ScenarioService $service)
    {
        $this->service = $service;
    }


    public function index(Request $request)
    {
        // Ambil kolom penting saja untuk list view
        $scenarios = Scenario::select(
            'id', 
            'title', 
            'category', 
            'difficulty', 
            'created_at'
        )
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($scenarios);
    }

    public function show(Scenario $scenario)
    {
        // Muat relasi 'options' agar pilihan jawaban (A, B, C) ikut terkirim
        $scenario->load('options');

        return response()->json($scenario);
    }


    public function submit(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'player_id' => 'required|string|exists:players,PlayerId',
            'scenario_id' => 'required|string|exists:scenarios,id',
            'selected_option' => 'required|string', // Misal: 'A', 'B'
            'decision_time_seconds' => 'required|integer',
            
            // Validasi data nested (opsional tapi disarankan)
            'session_context' => 'required|array',
            'session_context.session_id' => 'required|string|exists:sessions,sessionId',
            'behavioral_signals' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            // 2. Panggil Service untuk memproses jawaban
            $result = $this->service->processSubmission($validator->validated());

            // 3. Kembalikan hasil (Skor baru, Intervensi, dll)
            return response()->json($result);

        } catch (\Exception $e) {
            // Tangani error jika skenario/opsi tidak valid dalam logika bisnis
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
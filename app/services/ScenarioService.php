<?php

namespace App\Services;

use App\Repositories\PlayerDecisionRepository;
use App\Repositories\PlayerProfileRepository;
use App\Models\ScenarioOption;
use App\Models\Scenario;

class ScenarioService
{
    protected $decisionRepo;
    protected $profileRepo;

    public function __construct(
        PlayerDecisionRepository $decisionRepo, 
        PlayerProfileRepository $profileRepo
    ) {
        $this->decisionRepo = $decisionRepo;
        $this->profileRepo = $profileRepo;
    }

    public function processSubmission(array $data)
    {
        // 1. Ambil Kunci Jawaban dari Database
        $option = ScenarioOption::where('scenarioId', $data['scenario_id'])
            ->where('optionId', $data['selected_option'])
            ->first();

        if (!$option) {
            throw new \Exception("Opsi jawaban tidak ditemukan.");
        }

        // 2. Hitung Skor Baru
        $scoreChangeArray = $option->scoreChange; // JSON dari DB: {"utang": 10, "overall": 10}
        $scoreImpact = $scoreChangeArray['overall'] ?? 0; // Ambil nilai overall untuk log sederhana

        // 3. Update Profil Pemain (Lifetime Scores)
        $updatedScores = $this->updatePlayerScores($data['player_id'], $scoreChangeArray);

        // 4. Simpan Keputusan ke 'player_decisions'
        $this->decisionRepo->store([
            'player_id' => $data['player_id'],
            'session_id' => $data['session_context']['session_id'] ?? 'unknown',
            'turn_number' => $data['session_context']['turn_id'] ?? 0, // Simplifikasi, ambil int nya saja nanti
            'content_id' => $data['scenario_id'],
            'selected_option' => $data['selected_option'],
            'is_correct' => $option->is_correct,
            'score_change' => $scoreImpact,
            'decision_time_seconds' => $data['decision_time_seconds'],
            
            // Sinyal AI (Mocking Logic Sederhana)
            'intervention_triggered' => $this->checkInterventionNeeded($data),
            'intervention_level' => $this->checkInterventionNeeded($data) ? 3 : 0
        ]);

        // 5. Siapkan Respons AI & Intervensi
        $intervention = null;
        if ($this->checkInterventionNeeded($data)) {
            $intervention = [
                'level' => 3,
                'message' => "PAUSE! Keputusan ini berisiko tinggi.",
                'actions' => ["show_reflection"]
            ];
        }

        return [
            'player_id' => $data['player_id'],
            'scenario_id' => $data['scenario_id'],
            'correct' => $option->is_correct,
            'score_change' => $scoreImpact,
            'updated_scores' => $updatedScores,
            'prediction' => [
                'probability_correct' => 0.072, // Mock value
                'risk_level' => 'CRITICAL'
            ],
            'intervention' => $intervention
        ];
    }

    // Logika Internal: Update Skor JSON
    private function updatePlayerScores($playerId, $changes)
    {
        $profile = $this->profileRepo->findProfile($playerId);
        $currentScores = $profile->lifetime_scores ?? [];

        foreach ($changes as $category => $value) {
            if (!isset($currentScores[$category])) {
                $currentScores[$category] = 0;
            }
            $currentScores[$category] += $value;
        }

        $this->profileRepo->updateScores($playerId, $currentScores);
        return $currentScores;
    }

    // Logika Internal: Cek apakah butuh intervensi (Sederhana)
    private function checkInterventionNeeded($data)
    {
        $signals = $data['behavioral_signals'] ?? [];
        // Contoh: Jika error streak >= 3, trigger intervensi
        return ($signals['error_streak'] ?? 0) >= 3;
    }
}
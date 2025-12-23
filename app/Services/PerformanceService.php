<?php

namespace App\Services;

use App\Models\PlayerProfile;
use App\Services\AI\CosineSimilarityService;
use Illuminate\Support\Facades\DB;

class PerformanceService
{
    /**
     * Mendapatkan skor performa pemain berdasarkan ID pemain
     */
    public function getPerformanceScores(string $playerId)
    {
        $profile = PlayerProfile::find($playerId);

        if (!$profile) {
            return null;
        }

        $scores = $profile->lifetime_scores ?? [];
        if (is_string($scores)) {
            $scores = json_decode($scores, true) ?? [];
        }

        $overall = $scores['overall'] ?? 0;
        if ($overall == 0 && !empty($scores)) {
            $numericScores = array_filter($scores, 'is_numeric');
            if (count($numericScores) > 0) {
                $overall = array_sum($numericScores) / count($numericScores);
            }
        }

        return [
            'scores' => [
                'pendapatan' => $scores['pendapatan'] ?? 0,
                'anggaran' => $scores['anggaran'] ?? 0,
                'tabungan_dan_dana_darurat' => $scores['tabungan_dan_dana_darurat'] ?? 0,
                'utang' => $scores['utang'] ?? 0,
                'investasi' => $scores['investasi'] ?? 0,
                'asuransi_dan_proteksi' => $scores['asuransi_dan_proteksi'] ?? 0,
                'tujuan_jangka_panjang' => $scores['tujuan_jangka_panjang'] ?? 0,
                'overall' => round($overall)
            ],
            'last_updated' => $profile->last_updated ? $profile->last_updated->toIso8601String() : null
        ];
    }
}
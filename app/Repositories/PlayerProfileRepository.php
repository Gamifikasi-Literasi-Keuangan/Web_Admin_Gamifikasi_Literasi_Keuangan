<?php

namespace App\Repositories;

use App\Models\PlayerProfile;

class PlayerProfileRepository
{
    public function findThresholdsByPlayerId(string $playerId)
    {
        return PlayerProfile::where('PlayerId', $playerId)
            ->select('PlayerId', 'thresholds')
            ->first();
    }

    // --- FUNGSI BARU ---
    public function updateThresholds(string $playerId, array $newThresholds)
    {
        // Update kolom thresholds dengan data baru
        return PlayerProfile::where('PlayerId', $playerId)
            ->update(['thresholds' => $newThresholds]);
    }
}
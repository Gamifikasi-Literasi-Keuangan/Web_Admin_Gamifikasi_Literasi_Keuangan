<?php

namespace App\Repositories;

use App\Models\ProfilingResult;
use App\Models\PlayerProfile;

class ProfilingRepository
{
    public function saveResult($playerId, $fuzzy, $ann, $finalClass, $recommendation)
    {
        return ProfilingResult::create([
            'player_id' => $playerId,
            'fuzzy_output' => json_encode($fuzzy),
            'ann_output' => json_encode($ann),
            'final_class' => $finalClass,
            'recommended_focus' => json_encode($recommendation),
        ]);
    }

    public function updateProfile($playerId, $fuzzy, $ann, $finalClass)
    {
        return PlayerProfile::updateOrCreate(
            ['PlayerId' => $playerId],
            [
                'cluster' => $finalClass,
                'fuzzy_scores' => json_encode($fuzzy),
                'ann_probabilities' => json_encode($ann),
                'last_updated' => now(),
            ]
        );
    }
}

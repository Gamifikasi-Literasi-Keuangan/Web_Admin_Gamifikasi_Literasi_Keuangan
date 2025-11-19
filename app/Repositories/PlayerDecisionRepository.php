<?php

namespace App\Repositories;

use App\Models\PlayerDecision;

class PlayerDecisionRepository
{
    public function store(array $data)
    {
        // Pastikan nilai default jika tidak ada
        return PlayerDecision::create(array_merge([
            'content_type' => 'scenario',
            'turn_number' => 0,
            'intervention_triggered' => false,
            'intervention_level' => 0,
            'created_at' => now()
        ], $data));
    }
}
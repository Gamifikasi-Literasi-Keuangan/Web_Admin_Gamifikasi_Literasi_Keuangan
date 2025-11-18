<?php

namespace App\Repositories;

use App\Models\Telemetry;

class FeedbackRepository
{
    // Mencatat log feedback ke tabel telemetry
    public function logIntervention(array $data)
    {
        return Telemetry::create([
            'playerId' => $data['player_id'],
            // Gunakan session_id dari context, atau null jika tidak ada
            'sessionId' => $data['session_context']['session_id'] ?? null, 
            'action' => 'intervention_feedback',
            'details' => $data, // Laravel otomatis convert array ke JSON
            'created_at' => now()
        ]);
    }
}
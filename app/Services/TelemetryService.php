<?php

namespace App\Services;

use App\Models\Telemetry;
use Illuminate\Support\Facades\DB;

class TelemetryService
{
    public static function log(string $sessionId, string $playerId, string $action, array $details = [])
    {
        DB::table('telemetry')->insert([
            'session_id' => $sessionId,
            'player_id'  => $playerId,
            'action'     => $action,
            'details'    => json_encode($details),
            'created_at' => now()
        ]);
    }
}
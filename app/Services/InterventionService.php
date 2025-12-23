<?php

namespace App\Services;

use App\Models\PlayerProfile;
use App\Models\PlayerDecision;
use App\Models\InterventionTemplate;
use Illuminate\Support\Str;

class InterventionService
{
    /**
     * Cek apakah intervensi perlu ditrigger berdasarkan performa player
     */
    public function checkInterventionTrigger(string $playerId)
    {
        $recentDecisions = PlayerDecision::where('player_id', $playerId)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        $consecutiveErrors = 0;
        foreach ($recentDecisions as $decision) {
            if (!$decision->is_correct) {
                $consecutiveErrors++;
            } else {
                break;
            }
        }

        $triggerLevel = 0;
        if ($consecutiveErrors >= 3) {
            $triggerLevel = 2;
        } elseif ($consecutiveErrors == 2) {
            $triggerLevel = 1;
        }

        if ($triggerLevel == 0) {
            return null;
        }

        $template = InterventionTemplate::where('level', $triggerLevel)->first();

        if (!$template) {
            return [
                'intervention_id' => 'intv_' . Str::random(6),
                'intervention_level' => $triggerLevel,
                'title' => 'Peringatan Risiko',
                'message' => "⚠️ Kamu sudah $consecutiveErrors kali salah berturut-turut. Yuk pelan-pelan!",
                'options' => [
                    ['id' => 'heed', 'text' => 'Lihat Tips'],
                    ['id' => 'ignore', 'text' => 'Lanjut Saja']
                ]
            ];
        }

        $dbOptions = $template->actions_template ?? [];

        // Force IDs to match frontend expectations
        if (isset($dbOptions[0])) {
            $dbOptions[0]['id'] = 'heed';
        }
        if (isset($dbOptions[1])) {
            $dbOptions[1]['id'] = 'ignore';
        }

        return [
            'intervention_id' => 'intv_' . Str::random(6),
            'intervention_level' => $template->level,
            'title' => $template->title_template,
            'message' => $template->message_template,
            'options' => $dbOptions
        ];
    }
}
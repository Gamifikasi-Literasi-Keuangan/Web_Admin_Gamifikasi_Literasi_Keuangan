<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EndTurnRequest extends FormRequest
{
    public function rules(): array {
        return [
            'turn_id' => 'required|string|exists:turns,turn_id',
            'player_id' => 'required|string|exists:players,player_id',
            'session_id' => 'required|string|exists:sessions,session_id',
            'tile_id' => 'required|integer',
            'tile_type' => 'required|string',
            
            'actions' => 'required|array',
            'actions.*.action' => 'required|string',
            'actions.*.value' => 'nullable',
            'actions.*.tile_id' => 'nullable|integer',
            'actions.*.scenario_id' => 'nullable|integer',
            'actions.*.pilihan' => 'nullable|string',
            'actions.*.correct' => 'nullable|boolean',
            'actions.*.score_change' => 'nullable|integer',

            'turn_ended_at' => 'required|date'
        ];
    }
}
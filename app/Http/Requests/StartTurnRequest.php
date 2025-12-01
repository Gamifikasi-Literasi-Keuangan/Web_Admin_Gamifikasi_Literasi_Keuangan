<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartTurnRequest extends FormRequest
{
    public function rules(): array {
    return [
        'session_id' => 'required|string|exists:sessions,session_id',
        'player_id' => 'required|string|exists:players,player_id',
        'turn_number' => 'required|integer|min:1',
        'timestamp' => 'required|date'
    ];
    }
}

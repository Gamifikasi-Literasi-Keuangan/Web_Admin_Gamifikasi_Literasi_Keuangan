<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovePlayerRequest extends FormRequest
{
    public function rules(): array {
    return [
        'session_id' => 'required|string|exists:sessions,session_id',
        'player_id' => 'required|string|exists:players,player_id',
        'from_tile' => 'required|integer|min:0|max:39',
        'steps' => 'required|integer|min:1|max:12',
    ];
    }
}
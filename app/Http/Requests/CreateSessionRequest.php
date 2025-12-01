<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSessionRequest extends FormRequest
{
    public function rules(): array {
    return [
        'host_player_id' => 'required|string|exists:players,player_id',
        'player_id' => 'required|array|exists:players,player_id',
        'max_turns' => 'required|integer|min:1',
    ];
    }
}
<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'sessions';
    protected $primaryKey = 'session_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = true;
    const CREATED_AT = 'started_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'session_id',
        'host_player_id',
        'max_players',
        'max_turns',
        'current_player_id',
        'turn_index',
        'status',
        'current_turn',
        'game_state',
        'started_at',
        'ended_at',
        'created_at'
    ];

    public function currentPlayer() {
        return $this->belongsTo(Player::class, 'current_player_id', 'player_id');
    }

    public function players() {
        return $this->belongsToMany(Player::class, 'participatesin', 'session_id', 'player_id')
                    ->withPivot('player_order', 'position_index', 'score', 'connection_status', 'is_ready', 'rank', 'joined_at');

    }

    public function turns() {
        return $this->hasMany(Turn::class, 'session_id', 'session_id');
    }
}

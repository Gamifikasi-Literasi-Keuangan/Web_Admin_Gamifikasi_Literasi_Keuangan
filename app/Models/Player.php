<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $table = 'players';
    protected $primaryKey = 'player_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function profile()
    {
        return $this->hasOne(PlayerProfile::class, 'player_id', 'player_id');
    }

    public function gameSessions() {
        return $this->belongsToMany(Session::class, 'participatesin', 'player_id', 'session_id')
                    ->withPivot('player_order', 'position_index', 'score', 'connection_status', 'is_ready', 'rank', 'joined_at');
    }
}

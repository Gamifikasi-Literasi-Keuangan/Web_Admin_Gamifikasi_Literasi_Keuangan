<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipatesIn extends Model
{
    protected $table = 'participatesin';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'session_id',
        'player_id',
        'player_order',
        'position_index',
        'score',
        'connection_status',
        'is_ready',
        'rank',
        'joined_at'
    ];

    public $timestamps = false;
}

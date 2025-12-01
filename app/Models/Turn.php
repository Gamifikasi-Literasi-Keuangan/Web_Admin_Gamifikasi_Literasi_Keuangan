<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turn extends Model
{
    protected $table = 'turns';
    protected $primaryKey = 'turn_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'turn_id',
        'session_id',
        'player_id',
        'turn_number',
        'started_at',
        'ended_at'
    ];
}

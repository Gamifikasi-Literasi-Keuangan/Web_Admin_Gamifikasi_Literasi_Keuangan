<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telemetry extends Model
{
    protected $table = 'telemetry';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'session_id',
        'player_id',
        'turn_id',
        'tile_id',
        'action',
        'details',
        'metadata',
        'created_at'
    ];

}
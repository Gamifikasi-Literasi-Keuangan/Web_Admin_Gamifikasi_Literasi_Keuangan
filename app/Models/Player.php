<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;
    protected $table = 'players';
    protected $primaryKey = 'PlayerId';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'PlayerId',
        'user_id',
        'name',
        'avatar_url',
        'gamesPlayed',
        'initial_platform',
        'locale',
        'updated_at',
        'created_at'
    ];

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null;

    public function participations()
    {
        return $this->hasMany(ParticipatesIn::class, 'playerId', 'PlayerId');
    }

    public function profile()
    {
        return $this->hasOne(PlayerProfile::class, 'PlayerId', 'PlayerId');
    }

    public function gameSessions() {
        return $this->belongsToMany(Session::class, 'ParticipatesIn', 'playerId', 'sessionId')
                    ->withPivot('score', 'position', 'color', 'status');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerDecision extends Model
{
    use HasFactory;

    protected $table = 'player_decisions';
    public $timestamps = false; // Kita isi 'timestamp' manual/default

    protected $fillable = [
        'player_id',
        'session_id',
        'turn_number',
        'content_type',
        'content_id',
        'selected_option',
        'is_correct',
        'score_change',
        'decision_time_seconds',
        'intervention_triggered',
        'intervention_level',
        'created_at'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'intervention_triggered' => 'boolean',
        'created_at' => 'datetime',
    ];
}
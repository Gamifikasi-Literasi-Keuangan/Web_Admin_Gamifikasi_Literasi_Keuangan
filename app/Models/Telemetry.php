<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Telemetry extends Model
{
    use HasFactory;

    protected $table = 'telemetry';
    public $timestamps = false; // Kita atur created_at manual

    protected $fillable = [
        'sessionId',
        'playerId',
        'action',
        'details',   // Disimpan sebagai TEXT/JSON
        'metadata',  // Disimpan sebagai JSON
        'created_at'
    ];

    protected $casts = [
        'details' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];
}
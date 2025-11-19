<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipatesIn extends Model
{
    use HasFactory;

    // Beri tahu Eloquent nama tabel yang benar
    protected $table = 'ParticipatesIn';

    // Tabel ini tidak punya timestamp 'created_at'/'updated_at'
    public $timestamps = false;

    /**
     * Relasi (Langkah 6 di diagram): Baris ini milik SATU Player.
     * Ini adalah kunci untuk JOIN kita!
     */
    public function player()
    {
        return $this->belongsTo(Player::class, 'playerId', 'PlayerId');
    }
}
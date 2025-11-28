<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScenarioOption extends Model
{
    use HasFactory;

    // Tentukan nama tabel yang benar
    protected $table = 'scenario_options';

    // Tidak ada timestamp 'created_at'/'updated_at'
    public $timestamps = false;

    /**
     * Otomatis cast kolom JSON (MySQL)
     */
    protected $casts = [
        'scoreChange' => 'array',
    ];

    /**
     * RELASI: Opsi ini milik SATU Skenario.
     */
    public function scenario()
    {
        return $this->belongsTo(Scenario::class, 'scenarioId', 'id');
    }
}
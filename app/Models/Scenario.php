<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scenario extends Model
{
    use HasFactory;

    // Tentukan nama tabelnya (Laravel mungkin mengira 'scenarios')
    protected $table = 'scenarios';

    // Beri tahu Eloquent bahwa PK BUKAN 'id' integer
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Atur timestamp
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null; // Tidak ada 'updated_at'

    /**
     * Otomatis cast kolom JSON (MySQL)
     */
    protected $casts = [
        'tags' => 'array',
        'weak_area_relevance' => 'array',
        'cluster_relevance' => 'array',
    ];

    /**
     * RELASI (Langkah 6 di diagram):
     * Satu Skenario memiliki BANYAK Opsi Jawaban.
     */
    public function options()
    {
        return $this->hasMany(ScenarioOption::class, 'scenarioId', 'id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cards extends Model
{
    protected $table = 'cards';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'id',
        'type',
        'title',
        'narration',
        'scoreChange',
        'action',
        'categories',
        'tags',
        'difficulty',
        'learning_objective',
        'weak_area_relevance',
        'cluster_relevance',
        'historical_success_rate'
    ];

    /**
     * Pastikan kolom JSON otomatis ter-cast menjadi array
     */
    protected $casts = [
        'categories'            => 'array',
        'tags'                  => 'array',
        'weak_area_relevance'   => 'array',
        'cluster_relevance'     => 'array',
    ];


    /**
     * RELASI OPSIONAL (hanya jika kamu punya tabel opsi kuis seperti quiz_options)
     * 
     * Misalnya:
     * cards.id → quiz_options.card_id
     */
    public function options()
    {
        return $this->hasMany(QuizOption::class, 'card_id', 'id');
    }
}

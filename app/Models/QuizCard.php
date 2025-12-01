<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
    
class QuizCard extends Model
{
    protected $table = 'quiz_cards';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'question',
        'correctOption',
        'correctScore',
        'incorrectScore',
        'tags',
        'difficulty',
        'learning_objective',
        'weak_area_relevance',
        'cluster_relevance',
        'historical_success_rate',
        'categories',
        'intervention'
    ];

    protected $casts = [
        'tags' => 'array',
        'weak_area_relevance' => 'array',
        'cluster_relevance' => 'array',
        'categories' => 'array',
        'intervention' => 'boolean',
    ];

    public function options()
    {
        return $this->hasMany(QuizOption::class, 'quizId', 'id');
    }
}
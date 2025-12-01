<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizOption extends Model
{
    protected $table = 'quiz_options';
    public $timestamps = false;

    protected $fillable = [
        'quizId',
        'optionId',
        'text'
    ];

    public function quiz()
    {
        return $this->belongsTo(QuizCard::class, 'quizId', 'id');
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizRequest extends FormRequest
{
    public function rules(): array {
        return [
            'quiz_id' => 'required|string|exists:quiz_cards,id',
            'selected_option' => 'required|string|size:1',
            'decision_time_seconds' => 'required|numeric|min:0',
        ];
    }
}

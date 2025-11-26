<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfilingSubmitRequest extends FormRequest
{
    public function rules()
    {
        return [
            'player_id' => 'required|string',
            'answers'   => 'required|array|min:3',
            'answers.*' => 'string',
            'platform'  => 'required|string',
            'locale'    => 'required|string',
            'session_id'=> 'required|string',
        ];
    }

    public function authorize()
    {
        return true;
    }
}

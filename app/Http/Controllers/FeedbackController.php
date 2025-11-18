<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\FeedbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    protected $service;

    public function __construct(FeedbackService $service)
    {
        $this->service = $service;
    }

    // IMPLEMENTASI API 28: POST /feedback/intervention
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'player_id' => 'required|string|exists:players,PlayerId',
            'player_response' => 'required|string', // 'ignored', 'heeded'
            'actual_decision' => 'required|string', // 'correct', 'incorrect'
            'session_context' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $result = $this->service->processFeedback($validator->validated());

        return response()->json($result);
    }
}
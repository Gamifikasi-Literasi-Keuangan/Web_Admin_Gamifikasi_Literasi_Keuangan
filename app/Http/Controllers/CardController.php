<?php

namespace App\Http\Controllers;

use App\Services\CardService; // <-- Impor Service
use App\Http\Requests\SubmitQuizRequest;
use Illuminate\Http\Request;

class CardController extends Controller
{
    // Suntikkan (inject) CardService lewat constructor
    public function __construct(protected CardService $cardService)
    {
    }

    /**
     * Implementasi Diagram No. 17: GET /card/quiz/{id}
     */
    public function getQuizCard($id)
    {
        return response()->json($this->cardService->getQuizCard($id));
    }

    public function getRiskCard($id)
    {
        return response()->json($this->cardService->getRiskCard($id));
    }

    public function getChanceCard($id)
    {
        return response()->json($this->cardService->getChanceCard($id));
    }

    public function submitQuiz(SubmitQuizRequest $request)
    {
        $result = $this->cardService->submitQuiz(
            $request->quiz_id,
            $request->selected_option,
            $request->decision_time_seconds
        );

        return response()->json($result);
    }
}
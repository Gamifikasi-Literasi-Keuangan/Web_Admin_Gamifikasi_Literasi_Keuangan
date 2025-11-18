<?php

namespace App\Services;

use App\Repositories\FeedbackRepository;
use App\Services\ThresholdService;

class FeedbackService
{
    protected $feedbackRepo;
    protected $thresholdService;

    // Inject ThresholdService agar kita bisa update threshold dari sini
    public function __construct(FeedbackRepository $feedbackRepo, ThresholdService $thresholdService)
    {
        $this->feedbackRepo = $feedbackRepo;
        $this->thresholdService = $thresholdService;
    }

    public function processFeedback(array $data)
    {
        // 1. Selalu Catat Log
        $this->feedbackRepo->logIntervention($data);

        $effectivenessUpdated = false;

        // 2. LOGIKA AI: Jika user "bebal" (Abaikan peringatan + Salah Jawab)
        // Maka sistem harus lebih sensitif di masa depan.
        if (
            ($data['player_response'] ?? '') === 'ignored' &&
            ($data['actual_decision'] ?? '') === 'incorrect'
        ) {
            // Panggil logika update threshold
            $this->thresholdService->increaseSensitivity($data['player_id']);
            $effectivenessUpdated = true;
        }

        return [
            'logged' => true,
            'effectiveness_updated' => $effectivenessUpdated
        ];
    }
}
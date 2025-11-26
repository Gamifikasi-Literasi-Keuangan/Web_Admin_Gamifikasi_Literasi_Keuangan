<?php

namespace App\Services;

use App\Repositories\FeedbackRepository;

class FeedbackService
{
    protected $feedbackRepo;

    public function __construct(FeedbackRepository $feedbackRepo)
    {
        $this->feedbackRepo = $feedbackRepo;
    }

    public function processFeedback(array $data)
    {
        // Catat log intervention ke telemetry
        $this->feedbackRepo->logIntervention($data);

        return [
            'logged' => true
        ];
    }
}
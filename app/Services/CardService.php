<?php

namespace App\Services;

use App\Models\QuizCard;
use App\Models\QuizOption;
use App\Models\Cards;

class CardService
{
    public function getRiskCard(string $id)
    {
        // Ambil dari DB
        $card = Cards::where('id', $id)
                    ->where('type', 'risk')
                    ->firstOrFail();

        // Simulasi skor baru (contoh: nilai acak 5–10)
        $newScore = 6;

        // Dice pre-roll (0, 1, atau 2)
        $dice = rand(0, 2);

        return [
            "card_category"     => $card->categories,
            "title"             => $card->title,
            "narration"         => $card->narration,
            "score_change"      => $card->scoreChange,
            "affected_score"    => $card->categories,
            "new_score_value"   => $newScore,

            "dice_preroll_result" => $dice,
            "possible_tiles"      => $card->tags
        ];
    }

    function getChanceCard(string $id)
    {
        // Ambil dari DB
        $card = Cards::where('id', $id)
                    ->where('type', 'chance')
                    ->firstOrFail();

        $newScore = 8;

        $dice = 0;

        return [
            "card_category"     => $card->categories,
            "title"             => $card->title,
            "narration"         => $card->narration,
            "score_change"      => $card->scoreChange,
            "affected_score"    => $card->categories,
            "new_score_value"   => $newScore,

            "dice_preroll_result" => $dice,
            "possible_tiles"      => $card->tags
        ];
    }

    public function getQuizCard($id)
    {
        $card = QuizCard::with('options')->findOrFail($id);

        return [
            "card_category" => $card->categories[0] ?? null,  
            "question" => $card->question,

            "options" => $card->options->map(function($opt) {
                return [
                    "id" => $opt->optionId,
                    "text" => $opt->text
                ];
            }),

            "intervention" => $card->intervention ? true : false
        ];
    }

    public function submitQuiz(string $quizId, string $selectedOption, float $decisionTimeSeconds)
    {
        // 1. Ambil quiz card
        $quiz = QuizCard::with("options")->findOrFail($quizId);

        // 2. Benar atau salah
        $correct = strtoupper($selectedOption) === strtoupper($quiz->correctOption);

        // 3. Score change
        $scoreChange = $correct
            ? $quiz->correctScore
            : $quiz->incorrectScore;

        // 4. Ambil kategori pertama sebagai affected_score
        $affectedScore = $quiz->categories[0] ?? null;

        // 5. Nilai awal (sementara static, bisa nanti ambil dari DB)
        $initialScore = 12; // contoh
        $newScore = $initialScore + $scoreChange;

        return [
            "correct" => $correct,
            "score_change" => $scoreChange,
            "affected_score" => $affectedScore,
            "new_score_value" => $newScore
        ];
    }

}
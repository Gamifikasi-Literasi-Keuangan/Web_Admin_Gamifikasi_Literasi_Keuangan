<?php

namespace App\Services;

use App\Models\PlayerProfile;
use App\Services\AI\CosineSimilarityService;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    protected $cosine;

    public function __construct(CosineSimilarityService $cosine)
    {
        $this->cosine = $cosine;
    }

    /**
     * Logika 1: Cosine Similarity untuk Pertanyaan Selanjutnya
     */
    public function recommendNextQuestion(string $playerId)
    {
        $profile = PlayerProfile::find($playerId);
        if (!$profile || empty($profile->lifetime_scores)) {
            return ['error' => 'Player profile or scores not found'];
        }

        $userScores = json_decode($profile->lifetime_scores, true);
        
        $weakestCategory = $this->findWeakestCategory($userScores);
        $userWeakestScore = $userScores[$weakestCategory] ?? 0;

        $questions = DB::table('scenarios')
                        ->where('category', $weakestCategory)
                        ->get();

        if ($questions->isEmpty()) {
            return ['error' => 'No questions found for category: ' . $weakestCategory];
        }

        $bestQuestion = null;
        $maxSimilarity = -1;
        $userVector = $this->prepareVector($userScores);
        
        foreach ($questions as $question) {
            if ($question->difficulty <= $userWeakestScore) {
                continue;
            }

            $questionVector = $this->createQuestionVector($question->category, $question->difficulty, array_keys($userScores));
            $similarity = $this->cosine->calculate($userVector, $questionVector);

            if ($similarity > $maxSimilarity) {
                $maxSimilarity = $similarity;
                $bestQuestion = $question;
            }
        }

        if ($bestQuestion) {
            return [
                'recommendation' => $bestQuestion,
                'similarity_score' => $maxSimilarity,
                'reason' => "Based on your lowest score in $weakestCategory"
            ];
        }

        return ['error' => 'No suitable challenging question found'];
    }
    
    /**
     * Mencari kategori dengan skor terendah
     */
    private function findWeakestCategory(array $scores): string
    {
        asort($scores);
        return array_key_first($scores);
    }

    /**
     * Mempersiapkan vektor dari skor kategori untuk perhitungan cosine similarity
     */
    private function prepareVector(array $scores): array
    {
        $categories = ['pendapatan', 'anggaran', 'tabungan_dan_dana_darurat', 'utang', 'investasi', 'asuransi_dan_proteksi', 'tujuan_jangka_panjang'];
        $vector = [];
        foreach ($categories as $cat) {
            $vector[] = $scores[$cat] ?? 0;
        }
        return $vector;
    }

    /**
     * Membuat vektor pertanyaan berdasarkan kategori dan tingkat kesulitan
     */
    private function createQuestionVector(string $category, float $difficulty, array $allCategories): array
    {
        $normalizedCategory = strtolower(str_replace([' ', '&'], ['_', 'dan'], $category));
        
        $vector = [];
        $template = array_fill_keys(['pendapatan', 'anggaran', 'tabungan_dan_dana_darurat', 'utang', 'investasi', 'asuransi_dan_proteksi', 'tujuan_jangka_panjang'], 0);
        
        if (array_key_exists($normalizedCategory, $template)) {
            $template[$normalizedCategory] = $difficulty;
        }
        
        return array_values($template);
    }
}

<?php

namespace App\Services;

use App\Models\Card;
use App\Models\QuizCard;
use App\Models\PlayerProfile;
use App\Models\PlayerDecision;
use App\Models\ParticipatesIn;
use App\Services\InterventionService;
use Illuminate\Support\Facades\DB;

class CardService
{
    protected $interventionService;

    // Inject InterventionService via Constructor
    public function __construct(InterventionService $interventionService)
    {
        $this->interventionService = $interventionService;
    }

    /**
     * Mengambil kartu risiko dan mengaplikasikan dampaknya.
     */
    public function drawRiskCard(string $playerId, string $cardId)
    {
        return DB::transaction(function () use ($playerId, $cardId) {
            // 1. Cari Kartu Risiko
            $card = Card::where('id', $cardId)->where('type', 'risk')->first();

            if (!$card) {
                return ['error' => 'Risk card not found'];
            }

            // 2. Ambil Profil Pemain
            $profile = PlayerProfile::find($playerId);
            if (!$profile) {
                return ['error' => 'Player profile not found'];
            }

            // 3. Tentukan Kategori Terpengaruh
            // Ambil kategori pertama dari array categories di kartu (misal: ["Anggaran"])
            $categories = $card->categories ?? ['General'];
            $affectedCategoryKey = strtolower($categories[0] ?? 'pendapatan'); 
            
            $scoreKeyMap = [
                'anggaran' => 'anggaran',
                'pendapatan' => 'pendapatan',
                'tabungan' => 'tabungan',
                'utang' => 'utang',
                'investasi' => 'investasi',
                'asuransi' => 'asuransi_dan_proteksi',
                'risiko' => 'pengeluaran_lain'
            ];
            $targetScoreKey = $scoreKeyMap[$affectedCategoryKey] ?? $affectedCategoryKey;

            $currentScores = $profile->lifetime_scores ?? [];
            $oldValue = $currentScores[$targetScoreKey] ?? 0;
            $change = $card->scoreChange;
            $newValue = max(0, $oldValue + $change);

            $dicePreroll = null;
            $possibleTiles = null;

            if ($card->action === 'roll_loss' || $card->action === 'random_choice') {
                // Simulasi 3 kemungkinan hasil
                $possibleTiles = ["Makan", "Transportasi", "Nongkrong"];
                $dicePreroll = rand(0, 2); 
            }

            // 6. Simpan Perubahan ke Database
            $currentScores[$targetScoreKey] = $newValue;
            $profile->lifetime_scores = $currentScores;
            $profile->save();

            // (Opsional) Catat ke tabel player_decisions sebagai history
            $this->logCardHistory($playerId, $cardId, $change);

            // 7. Format Response
            return [
                'card_category' => ucfirst($affectedCategoryKey),
                'title' => $card->title,
                'narration' => $card->narration,
                'score_change' => $change,
                'affected_score' => $targetScoreKey,
                'new_score_value' => $newValue,
                'dice_preroll_result' => $dicePreroll, // null jika tidak ada dadu
                'possible_tiles' => $possibleTiles     // null jika tidak ada dadu
            ];
        });
    }

    private function logCardHistory($playerId, $cardId, $scoreChange)
    {
        // Cari sesi aktif untuk konteks log
        $participation = ParticipatesIn::where('playerId', $playerId)
            ->whereHas('session', fn($q) => $q->where('status', 'active'))
            ->first();

        PlayerDecision::create([
            'player_id' => $playerId,
            'session_id' => $participation->sessionId ?? 'unknown',
            'turn_number' => $participation->session->current_turn ?? 0,
            'content_id' => $cardId,
            'content_type' => 'risk_card',
            'is_correct' => 0, // Kartu tidak ada benar/salah
            'score_change' => $scoreChange,
            'created_at' => now()
        ]);
    }

    public function drawChanceCard(string $playerId, string $cardId)
    {
        return DB::transaction(function () use ($playerId, $cardId) {
            // 1. Cari Kartu Kesempatan
            $card = Card::where('id', $cardId)->where('type', 'chance')->first();

            if (!$card) {
                return ['error' => 'Chance card not found'];
            }

            // 2. Ambil Profil Pemain
            $profile = PlayerProfile::find($playerId);
            if (!$profile) {
                return ['error' => 'Player profile not found'];
            }

            // 3. Tentukan Kategori Terpengaruh
            $categories = $card->categories ?? ['General'];
            $affectedCategoryKey = strtolower($categories[0] ?? 'pendapatan');
            
            // Mapping key skor (sama seperti risk)
            $scoreKeyMap = [
                'anggaran' => 'anggaran',
                'pendapatan' => 'pendapatan',
                'tabungan' => 'tabungan',
                'utang' => 'utang',
                'investasi' => 'investasi',
                'asuransi' => 'asuransi_dan_proteksi',
                'pendidikan' => 'tujuan_jangka_panjang' // Contoh mapping baru
            ];
            $targetScoreKey = $scoreKeyMap[$affectedCategoryKey] ?? $affectedCategoryKey;

            // 4. Hitung Perubahan Skor (Biasanya Positif)
            $currentScores = $profile->lifetime_scores ?? [];
            $oldValue = $currentScores[$targetScoreKey] ?? 0;
            $change = $card->scoreChange; // Nilai dari DB (misal: +5)
            $newValue = $oldValue + $change;

            // 5. Logika Preroll / Possible Tiles
            $dicePreroll = null;
            $possibleTiles = null;

            // Jika action membutuhkan pilihan acak atau lemparan dadu
            if ($card->action === 'roll_gain' || $card->action === 'random_reward') {
                // Contoh logika dinamis (bisa juga ambil dari linked_content di DB)
                $possibleTiles = ["Bonus Kecil", "Bonus Besar", "Jackpot"];
                $dicePreroll = rand(0, 2);
            } else {
                // Jika action standar (langsung dapat), possible_tiles bisa diisi info target
                // Sesuai contoh request Anda: possible_tiles = ["Pendidikan"]
                $possibleTiles = ["Start"]; 
                $dicePreroll = 0; // Default index 0
            }

            // 6. Simpan Perubahan
            $currentScores[$targetScoreKey] = $newValue;
            $profile->lifetime_scores = $currentScores;
            $profile->save();

            // Catat History
            $this->logCardHistory($playerId, $cardId, $change);

            // 7. Format Response
            return [
                'card_category' => ucfirst($affectedCategoryKey),
                'title' => $card->title,
                'narration' => $card->narration,
                'score_change' => $change,
                'affected_score' => $targetScoreKey,
                'new_score_value' => $newValue,
                'dice_preroll_result' => $dicePreroll,
                'possible_tiles' => $possibleTiles
            ];
        });
    }

    public function getQuizCardDetail(string $playerId, string $quizId)
    {
        // 1. Ambil Data Kuis & Opsi
        $quiz = QuizCard::with(['options' => function($q) {
            $q->orderBy('optionId');
        }])->find($quizId);

        if (!$quiz) {
            return ['error' => 'Quiz card not found'];
        }

        // 2. Cek Intervensi
        // Apakah pemain ini sedang dalam kondisi "berisiko" (salah terus)?
        $interventionCheck = $this->interventionService->checkInterventionTrigger($playerId);
        $hasIntervention = !empty($interventionCheck);

        // 3. Tentukan Kategori (Ambil dari tags pertama atau default)
        $category = 'General';
        if (!empty($quiz->tags) && is_array($quiz->tags)) {
            $category = $quiz->tags[0];
        }

        // 4. Format Response
        return [
            'card_category' => ucfirst($category),
            'question' => $quiz->question,
            'options' => $quiz->options->map(function ($opt) {
                return [
                    'id' => $opt->optionId,
                    'text' => $opt->text
                ];
            }),
            'intervention' => $hasIntervention
        ];
    }

    public function submitQuizAnswer(string $playerId, array $data)
    {
        return DB::transaction(function () use ($playerId, $data) {
            $quizId = $data['quiz_id'];
            $selectedOption = $data['selected_option'];

            // 1. Ambil Data Kuis
            $quiz = QuizCard::find($quizId);
            if (!$quiz) {
                return ['error' => 'Quiz card not found'];
            }

            // 2. Cek Kebenaran Jawaban
            // Di DB, correctOption menyimpan ID opsi yang benar (misal "B")
            $isCorrect = ($quiz->correctOption === $selectedOption);
            
            // Tentukan perubahan skor
            $scoreChange = $isCorrect ? $quiz->correctScore : $quiz->incorrectScore;

            // 3. Ambil Profil Pemain
            $profile = PlayerProfile::find($playerId);
            if (!$profile) return ['error' => 'Player profile not found'];

            $categoryLabel = $quiz->tags[0] ?? 'pendapatan'; 

            // 5. Update Skor
            $currentScores = $profile->lifetime_scores ?? [];
            $oldVal = $currentScores[$categoryLabel] ?? 0;
            $newVal = $oldVal + $scoreChange;
            
            // Opsional: Cegah nilai negatif
            // $newVal = max(0, $newVal); 

            $currentScores[$categoryLabel] = $newVal;
            $profile->lifetime_scores = $currentScores;
            $profile->save();

            // 6. Catat History Keputusan
            $this->logQuizDecision($playerId, $quizId, $selectedOption, $isCorrect, $scoreChange, $data['decision_time_seconds'] ?? 0);

            // 7. Format Response
            return [
                'correct' => $isCorrect,
                'score_change' => $scoreChange,
                'affected_score' => $categoryLabel,
                'new_score_value' => $newVal
            ];
        });
    }

    /**
     * Helper: Log ke tabel player_decisions
     */
    private function logQuizDecision($playerId, $contentId, $selection, $isCorrect, $change, $time)
    {
        // Cari sesi aktif
        $participation = ParticipatesIn::where('playerId', $playerId)
            ->whereHas('session', fn($q) => $q->where('status', 'active'))
            ->with('session')
            ->first();

        $sessionId = $participation ? $participation->sessionId : 'unknown';
        $turnNum = $participation ? ($participation->session->current_turn ?? 0) : 0;

        PlayerDecision::create([
            'player_id' => $playerId,
            'session_id' => $sessionId,
            'turn_number' => $turnNum,
            'content_id' => $contentId,
            'content_type' => 'quiz',
            'selected_option' => $selection,
            'is_correct' => $isCorrect,
            'score_change' => $change,
            'decision_time_seconds' => $time,
            'created_at' => now()
        ]);
    }
}
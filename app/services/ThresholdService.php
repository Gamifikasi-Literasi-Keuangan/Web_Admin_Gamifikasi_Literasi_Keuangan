<?php

namespace App\Services;

use App\Repositories\PlayerProfileRepository;

class ThresholdService
{
    protected $profileRepository;

    public function __construct(PlayerProfileRepository $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    public function getPlayerThresholds(string $playerId)
    {
        $profile = $this->profileRepository->findThresholdsByPlayerId($playerId);
        if (!$profile) return null;
        return [
            'player_id' => $profile->PlayerId,
            'thresholds' => $profile->thresholds
        ];
    }

    // --- FUNGSI BARU UNTUK LOGIKA UPDATE ---
    public function increaseSensitivity(string $playerId)
    {
        // 1. Ambil data lama
        $profile = $this->profileRepository->findThresholdsByPlayerId($playerId);
        if (!$profile || empty($profile->thresholds)) return false;

        $currentThresholds = $profile->thresholds;

        // 2. Logika Penyesuaian: Naikkan batas 'critical' sebesar 5%
        // Artinya, probabilitas kesalahan lebih tinggi sedikit pun akan dianggap kritis
        if (isset($currentThresholds['critical'])) {
            $currentThresholds['critical'] = min(0.95, $currentThresholds['critical'] + 0.05);
        }

        // Kita set reason otomatis untuk sistem
        $reason = "system_auto_adjustment:ignored_warning";

        // 3. Simpan
        return $this->profileRepository->updateThresholds($playerId, $currentThresholds, $reason);
    }
    
    // Fungsi untuk update manual (API 30)
    public function manualUpdate(string $playerId, array $adjustments)
    {
         $profile = $this->profileRepository->findThresholdsByPlayerId($playerId);
         if (!$profile) return false;
         
         // Gabungkan data lama dengan adjustment baru
         $newThresholds = array_merge($profile->thresholds ?? [], $adjustments);
         // Jika admin tidak isi reason, beri default
         $reason = $reason ?? "manual_update_by_admin";
         
         return $this->profileRepository->updateThresholds($playerId, $newThresholds, $reason);
    }
}
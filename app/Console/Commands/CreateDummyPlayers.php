<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Player;
use App\Models\PlayerProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CreateDummyPlayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dummy-players {count=3 : The number of players to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create dummy players with non-expiring tokens for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->argument('count');
        $this->info("Creating {$count} dummy players...");

        $headers = ['Player ID', 'Username', 'Access Token'];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            // Make the last one the "infinite profiling" tester
            $isInfiniteProfiler = ($i === $count - 1);
            $data = $this->createPlayer($i + 1, $isInfiniteProfiler);
            $rows[] = $data;
        }

        $this->table($headers, $rows);
        $this->info("Done! Tokens have been set to expire in 100 years.");
    }

    private function createPlayer($index, $isInfiniteProfiler = false)
    {
        return DB::transaction(function () use ($index, $isInfiniteProfiler) {
            $randomStr = Str::random(4);
            $username = $isInfiniteProfiler ? 'dummy_profiling_tester' : "dummy_player_{$index}_{$randomStr}";
            $googleId = $isInfiniteProfiler ? 'dummy_google_infinite' : "dummy_google_{$index}_{$randomStr}";

            $user = User::create([
                'username' => $username,
                'google_id' => $googleId,
                'role' => 'player',
                'avatar' => "https://ui-avatars.com/api/?name={$username}",
            ]);

            $playerId = $isInfiniteProfiler ? 'player_dummy_profiling_infinite' : 'player_' . Str::random(8);

            $player = Player::create([
                'user_id' => $user->id,
                'PlayerId' => $playerId,
                'name' => $username,
                'avatar_url' => $user->avatar,
                'initial_platform' => 'web',
                'locale' => 'en_US',
                'gamesPlayed' => 0,
                'created_at' => now(),
            ]);

            PlayerProfile::create([
                'PlayerId' => $player->PlayerId,
                'cluster' => null,
                'confidence_level' => 0.0,
                'lifetime_scores' => json_encode([]),
                'thresholds' => json_encode(["critical" => 0.30, "high" => 0.50, "medium" => 0.70]),
                'last_updated' => now(),
            ]);

            // Create non-expiring token (set to 10 years to avoid MySQL TIMESTAMP 2038 overflow)
            $expiresAt = now()->addYears(10);
            $token = $user->createToken('dummy-player-token', ['*'], $expiresAt)->plainTextToken;

            return [$player->PlayerId, $username, $token];
        });
    }
}

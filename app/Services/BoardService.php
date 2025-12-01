<?php

namespace App\Services;

use App\Models\BoardTile;
use Illuminate\Support\Facades\DB;

class BoardService
{
    public function getTileDetails(int $position)
    {
        return BoardTile::where('position_index', $position)->firstOrFail();
    }
}

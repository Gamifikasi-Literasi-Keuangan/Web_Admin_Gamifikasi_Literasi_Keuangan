<?php

namespace App\Http\Controllers;

use App\Services\BoardService;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    // Suntikkan (inject) Service
    public function __construct(protected BoardService $boardService)
    {
    }

    /**
     * Implementasi: GET /tile/{id}
     */
    public function getTile($id)
    {
        $tileData = $this->boardService->getTileDetails($id);
        
        return response()->json($tileData);
    }
}

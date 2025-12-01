<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardTile extends Model
{
    protected $table = 'boardtiles';
    protected $primaryKey = 'tile_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'tile_id',
        'name',
        'category',
        'type',
        'linked_content',
        'position_index'
    ];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilingInput extends Model
{
    protected $fillable = ['player_id', 'feature', 'updated_at', 'created_at'];
}

<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AuthUser extends Authenticatable
{
    protected $table = 'auth_users';

    protected $fillable = [
        'username',
        'passwordHash',
        'role',
    ];

    protected $hidden = [
        'passwordHash',
    ];

    public function getAuthPassword()
    {
        return $this->passwordHash;
    }
}

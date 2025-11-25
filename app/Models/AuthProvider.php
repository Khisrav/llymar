<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthProvider extends Model
{
    protected $fillable = [
        'provider',
        'provider_id',
        'user_id',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'short_name', 
        'full_name', 
        'boss', 
        'boss_title', 
        'legal_address', 
        'email', 
        'phone', 
        'website', 
        'current_account', 
        'correspondent_account', 
        'bank_name', 
        'bank_address', 
        'bik',
    ];
}

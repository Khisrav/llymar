<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComissionCredits extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'order_id',
        'parent_id',
        'receipt',
        'type',
    ];
}

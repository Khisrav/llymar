<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleFactor extends Model
{
    protected $fillable = [
        'group_name',
        'name',
        'value',
        'reduction_factor_key',
    ];
}

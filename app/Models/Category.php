<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'reduction_factors'];
    
    protected $casts = [
        'reduction_factors' => 'array',
    ];
}

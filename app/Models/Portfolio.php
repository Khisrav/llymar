<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = [
        'title',
        'images',
        'area',
        'color',
        'glass',
        'location',
        'year',
        'description',
    ];
    
    protected $casts = ['images' => 'array'];
}

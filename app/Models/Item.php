<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'name', 'purchase_price', 'retail_price', 'category_id', 'img', 'unit',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseRecord extends Model
{
    protected $fillable = [
        'item_id',
        'quantity',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemProperty extends Model
{
    protected $fillable = [
        'item_id',
        'name',
        'value',
    ];
    
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}

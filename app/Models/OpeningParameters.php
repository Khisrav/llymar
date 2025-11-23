<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningParameters extends Model
{
    protected $fillable = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'i'];
    
    /**
     * Relationship: OrderOpening belongs to an Order.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

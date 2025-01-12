<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderOpening extends Model
{
    protected $fillable = [
        'order_id',
        'name',
        'type',
        'doors',
        'width',
        'height',
    ];
    
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

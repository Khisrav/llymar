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
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'i',
        'door_handle_item_id',
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
    
    /**
     * Relationship: OrderOpening belongs to a door handle Item.
     *
     * @return BelongsTo
     */
    public function doorHandle(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'door_handle_item_id');
    }
}

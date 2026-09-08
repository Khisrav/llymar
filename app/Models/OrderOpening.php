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
        'mp',
        'ot1',
        'ot2',
        'ot3',
        'ot4',
        'zr',
        'door_handle_item_id',
    ];

    protected $casts = [
        'a' => 'integer',
        'b' => 'float',
        'c' => 'float',
        'd' => 'float',
        'e' => 'float',
        'f' => 'float',
        'g' => 'float',
        'i' => 'float',
        'mp' => 'integer',
        'ot1' => 'integer',
        'ot2' => 'integer',
        'ot3' => 'integer',
        'ot4' => 'integer',
        'zr' => 'integer',
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

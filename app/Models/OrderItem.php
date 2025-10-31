<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'item_id',
        'quantity',
        'price',
        'checked',
    ];
    
    /**
     * The "boot" method of the model.
     * Automatically creates Warehouse records on create/delete.
     */
    public static function boot()
    {
        parent::boot();
    
        static::created(function ($model) {
            WarehouseRecord::create([
                'item_id'  => $model->item_id,
                'order_id' => $model->order_id,
                'warehouse_id' => 0, //should retrieve (user->company_id)->warehouse_id 
                'quantity' => -1 * $model->quantity,
            ]);
        });
        
        static::deleted(function ($model) {
            //delete warehouse record where order_id and item_id match
            WarehouseRecord::where('order_id', $model->order_id)->where('item_id', $model->item_id)->delete();
        });
    }

    /**
     * Relationship: OrderItem belongs to an Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Relationship: OrderItem belongs to an Item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    
    /**
     * Calculate the total price of this OrderItem using a specific factor.
     *
     * @param string $factor The factor to use (pz, p1, p2, p3, p4)
     * @return float
     */
    public function itemTotalPrice(string $factor = 'pz'): float
    {
        $item = $this->item;
        if (! $item) {
            return 0.0;
        }

        // Calculate total using Item::itemPrice with the specified factor
        return Item::itemPrice($item->id, $factor) * $this->quantity;
    }
}

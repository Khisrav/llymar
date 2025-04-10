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
                'quantity' => -$model->quantity,
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
     * Calculate the total price of this OrderItem.
     *
     * @return float
     */
    public function itemTotalPrice(): float
    {
        $item = $this->item;
        if (! $item) {
            return 0.0;
        }

        // Retrieve the Order and its associated User
        $order = Order::find($this->order_id);
        if (! $order) return 0.0;
        
        $user = User::find($order->user_id);
        if (! $user) return 0.0;

        // Get user keys
        $wholesaleFactor = WholesaleFactor::where('group_name', $user->wholesale_factor_key);
        
        // $wholesaleFactorKey = $wholesaleFactor->value('name'); //OPT1, OPT2... value
        $reductionFactorKey = $wholesaleFactor->value('reduction_factor_key'); //KU1, KU2... key

        // Fetch wholesale factor (default to 1 if not found)
        $wholesaleFactorValue = $wholesaleFactor->value('value') ?? 1.0;

        // Fetch reduction factors for this item's category
        $category = Category::find($item->category_id);
        $reductionFactors = $category ? $category->reduction_factors : [];

        // Find matching factor in the array (default to 1 if not found)
        $foundFactor = collect($reductionFactors)->firstWhere('key', $reductionFactorKey);
        $reductionFactorValue = $foundFactor['value'] ?? 1.0;

        // Final total = purchase price * quantity * factors
        return $item->purchase_price
            * $this->quantity
            * floatval($wholesaleFactorValue)
            * floatval($reductionFactorValue);
    }
}

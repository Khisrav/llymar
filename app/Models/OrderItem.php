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
     * Relationship: OrderItem belongs to an Order.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Relationship: OrderItem belongs to an Item.
     *
     * @return BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    
    /**
     * Get the total price of the order item.
     * 
     * @return float
    */
    public function itemTotalPrice(): float
    {
        $item = $this->item;
    
        if (!$item) { return 0; }
    
        $order = Order::find($this->order_id);
        $user = User::find($order->user_id);
    
        $opt = $user->wholesale_factor_key;
        $ku = $user->reduction_factor_key;
    
        $wholesaleFactor = WholesaleFactor::where('name', $opt)->first();
        $wholesaleFactor = [
            'name' => $opt,
            'value' => $wholesaleFactor['value'] ?? 1,
        ];
        
        $reductionFactors = Category::where('id', $item->category_id)->first()->reduction_factors;
        
        if ($reductionFactors) {
            $reductionFactor = floatval(collect($reductionFactors)->firstWhere('key', $ku)['value']);
            $reductionFactor = $reductionFactor ? $reductionFactor : 1;   
        } else $reductionFactor = 1;
        
        return $item->purchase_price * $this->quantity * $wholesaleFactor['value'] * $reductionFactor;    
    }
}

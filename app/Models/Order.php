<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'invoice_id',
        'invoice_status',
        'total_price',
        'comment',
        'status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'ral_code',
        'selected_factor',
    ];
    
    //do changes into warehouse_records when order is created/deleted
    public static function boot()
    {
        parent::boot();
    
        static::updated(function ($model) {
            if ($model->status == 'expired') {
                WarehouseRecord::where('order_id', $model->id)->delete();
            } else {
                $orderItems = $model->orderItems;
                $warehouseRecords = WarehouseRecord::where('order_id', $model->id)->get()->keyBy('item_id');
    
                foreach ($orderItems as $orderItem) {
                    if (isset($warehouseRecords[$orderItem->item_id])) {
                        $warehouseRecord = $warehouseRecords[$orderItem->item_id];
                        $warehouseRecord->quantity = $orderItem->quantity;
                        $warehouseRecord->save();
                    }
                }
            }
        });
    }


    /**
     * Relationship: Order belongs to a User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Order has many Order Items.
     *
     * @return HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relationship: Order has many Order Openings.
     *
     * @return HasMany
     */
    public function orderOpenings(): HasMany
    {
        return $this->hasMany(OrderOpening::class);
    }

    /**
     * Relationship: Order has many Contracts.
     *
     * @return HasMany
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get the first contract for this order.
     *
     * @return Contract|null
     */
    public function getContractAttribute()
    {
        return $this->contracts()->first();
    }
}

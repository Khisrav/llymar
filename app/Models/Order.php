<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use App\Models\CommissionCredit;

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
        'city',
        'when_started_working_on_it',
        'factory_id',
        'order_type',
        'logistics_company_id',
        'is_sketch_sent',
        'readiness_date',
        'glasses_area',
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
            
            // Calculate commission when order status changes to 'paid' or 'completed'
            if (($model->status === 'paid' && $model->getOriginal('status') !== 'paid') ||
                ($model->status === 'completed' && $model->getOriginal('status') !== 'completed')) {
                static::calculateCommission($model);
            }
        });
    }
    
    /**
     * Calculate and create commission credits when order is paid or completed.
     * This method only creates commissions if they don't already exist.
     */
    protected static function calculateCommission($order)
    {
        $user = $order->user;
        
        // Check if user has a parent (for commission hierarchy)
        if ($user && $user->parent_id && $user->reward_fee && $user->hasRole('Dealer')) {
            $parent = User::find($user->parent_id);
            
            // Only create commission if parent has ROP role
            if ($parent && $parent->hasRole('ROP')) {
                // Check if commission credit already exists for this order
                $existingCommission = CommissionCredit::where('order_id', $order->id)
                    ->where('user_id', $user->id)
                    ->where('parent_id', $parent->id)
                    ->first();
                
                if (!$existingCommission) {
                    $commissionAmount = ($order->total_price * $user->reward_fee) / 100;
                    
                    // Create commission credit record
                    CommissionCredit::create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'parent_id' => $parent->id,
                        'amount' => $commissionAmount,
                        'type' => 'accrual',
                    ]);
                    
                    Log::info("Commission credit created on order payment", [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'parent_id' => $parent->id,
                        'amount' => $commissionAmount
                    ]);
                } else {
                    Log::info("Commission credit already exists for order", [
                        'order_id' => $order->id,
                        'existing_commission_id' => $existingCommission->id
                    ]);
                }
            }
        }
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
    
    /**
     * Get the glass vendor code for this order.
     *
     * @return string
     */
    public function getGlassCodeAttribute()
    {
        $glass = $this->getGlass();
        return $glass ? $glass->abbreviation : '—';
    }
    
    /**
     * Get the handles for this order.
     *
     * @return string
     */
    public function getHandlesAttribute()
    {
        $items = $this->getAdditionalItems();
        $html = '';
        foreach ($items as $item) {
            $html .= ($item->item->abbreviation ?? substr($item->item->name, 0, 10) . '...') . ' - ' . $item->quantity . '' . $item->item->unit . ' <br>';
        }
        return $html;
    }

    /**
     * Relationship: Order has many Commission Credits.
     *
     * @return HasMany
     */
    public function commissionCredits(): HasMany
    {
        return $this->hasMany(CommissionCredit::class);
    }
    
    public function getGlass()
    {
        $glassItemIDs = Item::where('category_id', '1')->pluck('id')->toArray();
        foreach ($this->orderItems as $orderItem) {
            if (in_array($orderItem->item_id, $glassItemIDs)) {
                return $orderItem->item;
            }
        }
        return null;
    }
    
    public function getAdditionalItems()
    {
        Log::info('getAdditionalItems');
        //llymar system items, services and glass
        $notAdditionalItemIDs = Item::where('category_id', '2')
                                    ->orWhere('category_id', '26')
                                    ->orWhere('category_id', '1')
                                    ->pluck('id')
                                    ->toArray();
        $additionalItems = [];
        foreach ($this->orderItems as $orderItem) {
            if (!in_array($orderItem->item_id, $notAdditionalItemIDs)) {
                $additionalItems[] = $orderItem;
            }
        }
        return $additionalItems;
    }
}

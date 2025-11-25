<?php

namespace App\Models;

use App\Http\Controllers\CommissionCreditController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use App\Models\CommissionCredit;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'creator_id',
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
        'delivery_option',
        'company_id',
        'company_bill_id',
        'logistics_company_id',
        'is_sketch_sent',
        'readiness_date',
        'glasses_area',
        'is_completed',
        'is_packed',
        'is_sworn',
        'is_painted',
        'is_cut',
        'cut_status',
        'glass_acceptance',
        'sketched_at',
        'cut_at',
        'painted_at',
        'packed_at',
        'sworn_at',
        'glass_rework_at',
        'glass_complaint_at',
        'glass_ready_at',
        'completed_at',
        'technical_comment',
    ];

    protected $casts = [
        'sketched_at' => 'datetime',
        'cut_at' => 'datetime',
        'painted_at' => 'datetime',
        'packed_at' => 'datetime',
        'sworn_at' => 'datetime',
        'glass_rework_at' => 'datetime',
        'glass_complaint_at' => 'datetime',
        'glass_ready_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
    
    //do changes into warehouse_records when order is created/deleted
    public static function boot()
    {
        parent::boot();
    
        static::updated(function ($model) {
            Log::info("Order updated", ['order_id' => $model->id, 'status' => $model->status]);
            // if ($model->status == 'expired') {
            //     WarehouseRecord::where('order_id', $model->id)->delete();
            // } else if ($model->status == 'paid') {
            //     $orderItems = $model->orderItems;
            //     $warehouseRecords = WarehouseRecord::where('order_id', $model->id)->get()->keyBy('item_id');
            //     Log::info('warehouse record updated for that order');
            //     foreach ($orderItems as $orderItem) {
            //         if (isset($warehouseRecords[$orderItem->item_id])) {
            //             $warehouseRecord = $warehouseRecords[$orderItem->item_id];

            //             if ($warehouseRecord->quantity >= 0) { $warehouseRecord->quantity = $orderItem->quantity * (-1); } 
            //             else { $warehouseRecord->quantity = $orderItem->quantity; }
            //             Log::info($warehouseRecord);

            //             $warehouseRecord->save();
            //         }
            //     }
            // }
            // if ($model->status === 'paid') {

            // }
            
            // Calculate commission when order status changes to 'paid' or 'completed'
            if (($model->status === 'paid' && $model->getOriginal('status') !== 'paid') ||
                ($model->status === 'completed' && $model->getOriginal('status') !== 'completed')) {
                Log::info("Calculating commission", ['order_id' => $model->id, 'status' => $model->status]);
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
        if ($order->status === 'paid' || $order->status === 'completed') {
            // Create commission credit
            $commissionCredit = new CommissionCreditController();
            $commissionCredit->store($order->id, $order->total_price);
        }
        // Log::info("Calculating commission", ['order_id' => $order->id]);
        // // $user = $order->user;
        // $user = User::find($order->user->id);
        
        // Log::info("User", ['user' => $user]);
        // Log::info("User parent_id", ['parent_id' => $user->parent_id]);
        // Log::info("User reward_fee", ['reward_fee' => $user->reward_fee]);
        // Log::info("User hasRole", ['hasRole' => $user->hasRole('Dealer')]);
        // // Log::info("User", ['user' => $user, 'parent_id' => $user->parent_id, 'reward_fee' => $user->reward_fee, 'hasRole' => $user->hasRole('Dealer')]);
        // // Check if user has a parent (for commission hierarchy)
        // if ($user && $user->parent_id && $user->reward_fee && $user->hasRole('Dealer')) {
        //     Log::info("User has a parent", ['user_id' => $user->id, 'parent_id' => $user->parent_id]);
        //     $parent = User::find($user->parent_id);
            
        //     // Only create commission if parent has ROP role
        //     if ($parent && $parent->hasRole('ROP')) {
        //         Log::info("Parent has ROP role", ['parent_id' => $parent->id]);
        //         // Check if commission credit already exists for this order
        //         $existingCommission = CommissionCredit::where('order_id', $order->id)
        //             ->where('user_id', $user->id)
        //             ->where('parent_id', $parent->id)
        //             ->first();
                
        //         if (!$existingCommission) {
        //             Log::info("Commission credit does not exist", ['order_id' => $order->id, 'user_id' => $user->id, 'parent_id' => $parent->id]);
        //             $commissionAmount = ($order->total_price * $user->reward_fee) / 100;
                    
        //             // Create commission credit record
        //             Log::info("Creating commission credit record", ['order_id' => $order->id, 'user_id' => $user->id, 'parent_id' => $parent->id, 'amount' => $commissionAmount]);
        //             CommissionCredit::create([
        //                 'user_id' => $user->id,
        //                 'order_id' => $order->id,
        //                 'parent_id' => $parent->id,
        //                 'amount' => $commissionAmount,
        //                 'type' => 'accrual',
        //             ]);
                    
        //             Log::info("Commission credit created on order payment", [
        //                 'order_id' => $order->id,
        //                 'user_id' => $user->id,
        //                 'parent_id' => $parent->id,
        //                 'amount' => $commissionAmount
        //             ]);
        //         } else {
        //             Log::info("Commission credit already exists for order", [
        //                 'order_id' => $order->id,
        //                 'existing_commission_id' => $existingCommission->id
        //             ]);
        //         }
        //     }
        // }
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
     * Relationship: Order belongs to a Company.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relationship: Order belongs to a CompanyBill.
     *
     * @return BelongsTo
     */
    public function companyBill(): BelongsTo
    {
        return $this->belongsTo(CompanyBill::class);
    }

    /**
     * Relationship: Order belongs to a LogisticsCompany.
     *
     * @return BelongsTo
     */
    public function logisticsCompany(): BelongsTo
    {
        return $this->belongsTo(LogisticsCompany::class);
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
        $items = $this->getDoorHandleItems();
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
    
    public function getDoorHandleItems()
    {
        $doorHandleItemIDs = Item::whereIn('category_id', [29, 31, 32])
                                ->pluck('id')
                                ->toArray();
        $doorHandleItems = [];
        foreach ($this->orderItems as $orderItem) {
            if (in_array($orderItem->item_id, $doorHandleItemIDs)) {
                $doorHandleItems[] = $orderItem;
            }
        }
        return $doorHandleItems;
    }
}

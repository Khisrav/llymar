<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CommissionCredit extends Model
{
    protected $table = 'commission_credits'; // Updated table name
    
    protected $fillable = [
        'user_id', // User who received the commission
        'amount',
        'order_id',
        'parent_id', // User who initiated the order
        'receipt', // Receipt file path
        'type', // Type of commission (e.g., 'accrual', 'write-off')
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            // Delete the receipt file if it exists
            if ($model->receipt && Storage::disk('public')->exists($model->receipt)) {
                Storage::disk('public')->delete($model->receipt);
            }
        });
    }

    /**
     * Relationship: CommissionCredit belongs to a User (initiator).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: CommissionCredit belongs to an Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Relationship: CommissionCredit belongs to a User (recipient).
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
} 
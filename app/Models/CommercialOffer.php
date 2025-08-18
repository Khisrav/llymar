<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialOffer extends Model
{
    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_comment',
        'manufacturer_name',
        'manufacturer_phone',
        'openings',
        'additional_items',
        'glass',
        'services',
        'cart_items',
        'total_price',
        'markup_percentage',
        'selected_factor',
    ];

    protected $casts = [
        'openings' => 'array',
        'additional_items' => 'array',
        'glass' => 'array',
        'services' => 'array',
        'cart_items' => 'array',
        'total_price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Item extends Model
{
    //начиная с апреля 2025 items это warehouse, т.е. детали это уже не каталог, а склад деталей
    protected $fillable = [
        'name',
        'purchase_price',
        'retail_price',
        'category_id',
        'img',
        'unit',
        'quantity_in_warehouse',
        'vendor_code',
        'is_for_llymar',
    ];

    /**
     * Relationship to Category model.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Relationship to WarehouseRecord model.
     */
    public function warehouseRecords()
    {
        return $this->hasMany(WarehouseRecord::class);
    }

    /**
     * Calculate the item price based on the user's wholesale factor
     * and the category's reduction factors. Utilizes caching to reduce
     * repeated DB queries.
     *
     * @param  int  $itemId
     * @return float
     */
    public static function itemPrice(int $itemId, string $user_wholesale_factor_key = null): float
    {
        // Retrieve the item or throw a 404 error if not found
        $item = self::findOrFail($itemId);
    
        // Determine the user's wholesale factor key
        $user_wholesale_factor_key = $user_wholesale_factor_key ?? Auth::user()->wholesale_factor_key;
    
        // Retrieve the wholesale factor and reduction factor key
        $wholesaleFactor = WholesaleFactor::where('group_name', $user_wholesale_factor_key);
        $reductionFactorKey = $wholesaleFactor->value('reduction_factor_key');
        
        // Cache the wholesale factor value for 60 minutes
        $wholesaleFactorValue = Cache::remember(
            "wholesale_factor_value_{$user_wholesale_factor_key}",
            60,
            fn() => $wholesaleFactor->value('value') ?? 1.0
        );
    
        // Cache the reduction factors for the item's category for 60 minutes
        $reductionFactors = Cache::remember(
            "category_{$item->category_id}_reduction_factors",
            60,
            fn() => $item->category?->reduction_factors ?? []
        );
    
        // Determine the reduction factor value
        $reductionFactorValue = collect($reductionFactors)
            ->firstWhere('key', $reductionFactorKey)['value'] ?? 1.0;
    
        Log::info("wholesale_factor_value: {$wholesaleFactorValue}, reduction_factor_value: {$reductionFactorValue}");
    
        // Calculate and return the final price
        return $item->purchase_price * $wholesaleFactorValue * $reductionFactorValue;
    }

}

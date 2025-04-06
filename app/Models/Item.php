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
    public static function itemPrice(int $itemId): float
    {
        // Find or fail will throw a 404 if not found
        /** @var self $item */
        $item = self::findOrFail($itemId);

        // Get logged-in user
        $user = Auth::user();

        // Retrieve the user's wholesale factor key & reduction factor key
        $wholesaleFactorKey = $user->wholesale_factor_key;
        $reductionFactorKey = $user->reduction_factor_key;

        // Cache the wholesale factor value for 60 minutes
        $wholesaleFactorValue = Cache::remember(
            "wholesale_factor_value_{$wholesaleFactorKey}",
            60,
            function () use ($wholesaleFactorKey) {
                return WholesaleFactor::where('name', $wholesaleFactorKey)->value('value') ?? 1.0;
            }
        );

        // Cache the reduction factors for the category for 60 minutes
        $reductionFactors = Cache::remember(
            "category_{$item->category_id}_reduction_factors",
            60,
            function () use ($item) {
                // If the item has a valid category relationship, return its reduction_factors
                return $item->category ? $item->category->reduction_factors : [];
            }
        );

        // Find the matching factor from the array; default to 1 if not found
        $reductionFactorValue = 1.0;
        if (! empty($reductionFactors)) {
            $foundFactor = collect($reductionFactors)->firstWhere('key', $reductionFactorKey);
            $reductionFactorValue = isset($foundFactor['value'])
                ? floatval($foundFactor['value'])
                : 1.0;
        }

        // Calculate and return the final price
        return $item->purchase_price * $wholesaleFactorValue * $reductionFactorValue;
    }
}

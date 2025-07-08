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
        'kz',
        'pz',
        'k1',
        'p1',
        'k2',
        'p2',
        'k3',
        'p3',
        'k4',
        'pr',
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
     * Relationship to ItemProperty model.
     */
    public function itemProperties()
    {
        return $this->hasMany(ItemProperty::class);
    }

    /**
     * Get the item price (simplified - just returns purchase price).
     *
     * @param  int  $itemId
     * @return float
     */
    public static function itemPrice(int $itemId): float
    {
        // Retrieve the item or throw a 404 error if not found
        $item = self::findOrFail($itemId);
    
        // Simply return the purchase price
        return $item->purchase_price;
    }

}

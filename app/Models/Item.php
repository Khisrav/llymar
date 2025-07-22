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
        'images',
        'description',
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

    protected $casts = [
        'images' => 'array',
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
     * Get the item price based on the selected factor.
     *
     * @param  int  $itemId
     * @param  string  $factor  The factor to use (kz, k1, k2, k3, k4)
     * @return float
     */
    public static function itemPrice(int $itemId, string $factor = 'kz'): float
    {
        // Retrieve the item or throw a 404 error if not found
        $item = self::findOrFail($itemId);
    
        // Get the appropriate factor value and price
        switch (strtolower($factor)) {
            case 'k1':
                return $item->purchase_price * ($item->k1 ?? 1.0);
            case 'k2':
                return $item->purchase_price * ($item->k2 ?? 1.0);
            case 'k3':
                return $item->purchase_price * ($item->k3 ?? 1.0);
            case 'k4':
                return $item->purchase_price * ($item->k4 ?? 1.0);
            case 'kz':
            default:
                return $item->purchase_price * ($item->kz ?? 1.0);
        }
    }

}

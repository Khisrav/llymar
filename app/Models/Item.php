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
        'pz',
        'p1',
        'p2',
        'p3',
        'pr',
        'weight',
        'abbreviation',
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
     * @param  string  $factor  The factor to use (pz, p1, p2, p3, p4)
     * @return float
     */
    public static function itemPrice(int $itemId, string $factor = 'pz'): float
    {
        // Retrieve the item or throw a 404 error if not found
        $item = self::findOrFail($itemId);
    
        // Get the appropriate price directly (no multiplication needed)
        switch (strtolower($factor)) {
            case 'p1':
                return $item->p1 ?? 0.0;
            case 'p2':
                return $item->p2 ?? 0.0;
            case 'p3':
                return $item->p3 ?? 0.0;
            case 'p4':
            case 'pr':
                return $item->pr ?? 0.0;
            case 'pz':
            default:
                return $item->pz ?? 0.0;
        }
    }

}

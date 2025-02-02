<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseRecord extends Model
{
    protected $fillable = [
        'item_id',
        'quantity',
    ];
    
    public function item(): BelongsTo {
        return $this->belongsTo(Item::class, 'id');
    }
    
    protected static function boot() {
        parent::boot();
        
        // запускается при внесении изменений в записях склада
        static::saved(function ($model) {
            if ($model->wasRecentlyCreated) {
                //if the row was created, adjust based on the new quantity
                $item = Item::find($model->item_id);
                $item->quantity_in_warehouse += $model->quantity;
                $item->save();
            } else {
                //if the row was updated, adjust based on the difference between old and new quantity
                $item = Item::find($model->item_id);
                $item->quantity_in_warehouse += $model->quantity - $model->getOriginal('quantity');
                $item->save();
            }
        });
        
        static::deleted(function ($model) {
            $item = Item::find($model->item_id);
            $item->quantity_in_warehouse -= $model->quantity;
            $item->save();
        });
    }
}

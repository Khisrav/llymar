<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $servicesIDs = Item::where('category_id', 35)->pluck('id');
        
        $orderItems = $order->orderItems;
        foreach ($orderItems as $item) {
            if ($servicesIDs->contains($item->item_id)) {
                $order->order_number = '4-' . $order->id;
                $order->save();
                break;
            }
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}

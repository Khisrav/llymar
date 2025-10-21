<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpireOldOrders extends Command
{
    protected $signature = 'orders:expire-old';
    protected $description = 'Expire orders that have been in "created" status for more than 14 days';

    public function handle()
    {
        $this->info('Checking for orders to expire...');
        
        // Find orders with status "created" that are older than 14 days
        $fourteenDaysAgo = Carbon::now()->subDays(14);
        
        $ordersToExpire = Order::where('status', 'created')
            ->where('created_at', '<=', $fourteenDaysAgo)
            ->get();
        
        $count = $ordersToExpire->count();
        
        if ($count === 0) {
            $this->info('No orders to expire.');
            return 0;
        }
        
        $this->info("Found {$count} order(s) to expire.");
        
        foreach ($ordersToExpire as $order) {
            try {
                $order->status = 'expired';
                $order->save();
                
                $this->info("Order #{$order->order_number} (ID: {$order->id}) expired.");
                
                Log::info("Order expired automatically", [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'created_at' => $order->created_at,
                    'expired_at' => now()
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to expire order #{$order->order_number}: " . $e->getMessage());
                
                Log::error("Failed to expire order", [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("Successfully expired {$count} order(s).");
        
        return 0;
    }
}


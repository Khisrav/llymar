<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionCredit;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CommissionCreditController extends Controller
{
    /**
     * Create a new commission credit record when an order is created.
     * This creates a "pending" commission that will be activated when payment is received.
     * 
     * @param int $order_id
     * @param float $total_price
     * @return void
     */
    public function store($order_id, $total_price) {
        try {
            $order = Order::find($order_id);
            if (!$order) {
                Log::error("Commission credit creation failed - Order not found", ['order_id' => $order_id]);
                return;
            }

            $user = $order->user;
            if (!$user) {
                Log::error("Commission credit creation failed - User not found", ['order_id' => $order_id]);
                return;
            }

            // Check if user has a parent (for commission hierarchy) and reward fee
            if ($user->parent_id && $user->hasRole('Dealer')) {
                $parent = User::find($user->parent_id);
                
                // Only create commission if parent has ROP role
                if ($parent && $parent->hasRole('ROP')) {
                    $commissionAmount = ($total_price * $parent->reward_fee) / 100;
                    
                    // Create commission credit record
                    $commissionCredit = CommissionCredit::create([
                        'user_id' => $user->id,          // Who created the order (initiator)
                        'order_id' => $order->id,        // Which order
                        'parent_id' => $parent->id,      // Who receives the commission
                        'amount' => $commissionAmount,   // Commission amount
                        'type' => 'accrual',            // Type of transaction
                    ]);

                    Log::info("Commission credit created on order creation", [
                        'commission_credit_id' => $commissionCredit->id,
                        'order_id' => $order->id,
                        'initiator_user_id' => $user->id,
                        'recipient_user_id' => $parent->id,
                        'amount' => $commissionAmount,
                        'reward_fee_percentage' => $parent->reward_fee,
                        'total_price' => $total_price
                    ]);
                } else {
                    Log::info("Commission credit not created - Parent user doesn't have ROP role", [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'parent_id' => $user->parent_id
                    ]);
                }
            } 
        } catch (\Exception $e) {
            Log::error("Commission credit creation failed", [
                'order_id' => $order_id,
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
        }
    }
}

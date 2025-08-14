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
     * Create a new commission credit record when an order is paid or completed.
     * This creates commission credits for eligible users based on order status.
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
            
            // Only create commission for paid or completed orders
            if (!in_array($order->status, ['paid', 'completed'])) {
                Log::info("Commission credit creation skipped - Order status not eligible", [
                    'order_id' => $order_id,
                    'status' => $order->status
                ]);
                return;
            }

            $user = $order->user;
            if (!$user) {
                Log::error("Commission credit creation failed - User not found", ['order_id' => $order_id]);
                return;
            }

            // Check if user has a parent (for commission hierarchy) and reward fee
            if ($user->parent_id && $user->reward_fee && $user->hasRole('Dealer')) {
                $parent = User::find($user->parent_id);
                
                // Only create commission if parent has ROP role
                if ($parent && $parent->hasRole('ROP')) {
                    // Check if commission credit already exists for this order
                    $existingCommission = CommissionCredit::where('order_id', $order->id)
                        ->where('user_id', $user->id)
                        ->where('parent_id', $parent->id)
                        ->first();
                    
                    if (!$existingCommission) {
                        // Use user's reward_fee, not parent's (consistent with Order model)
                        $commissionAmount = ($total_price * $user->reward_fee) / 100;
                        
                        // Create commission credit record
                        $commissionCredit = CommissionCredit::create([
                            'user_id' => $user->id,          // Who created the order (initiator)
                            'order_id' => $order->id,        // Which order
                            'parent_id' => $parent->id,      // Who receives the commission
                            'amount' => $commissionAmount,   // Commission amount
                            'type' => 'accrual',            // Type of transaction
                        ]);

                        Log::info("Commission credit created", [
                            'commission_credit_id' => $commissionCredit->id,
                            'order_id' => $order->id,
                            'order_status' => $order->status,
                            'initiator_user_id' => $user->id,
                            'recipient_user_id' => $parent->id,
                            'amount' => $commissionAmount,
                            'reward_fee_percentage' => $user->reward_fee,
                            'total_price' => $total_price
                        ]);
                    } else {
                        Log::info("Commission credit already exists for order", [
                            'order_id' => $order->id,
                            'existing_commission_id' => $existingCommission->id
                        ]);
                    }
                } else {
                    Log::info("Commission credit not created - Parent user doesn't have ROP role", [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'parent_id' => $user->parent_id
                    ]);
                }
            } else {
                Log::info("Commission credit not created - User doesn't have parent or isn't a Dealer", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'has_parent' => !empty($user->parent_id),
                    'is_dealer' => $user->hasRole('Dealer')
                ]);
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

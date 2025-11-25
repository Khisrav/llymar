<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionCredit;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CommissionCreditController extends Controller
{
    /**
     * Display the commission credits page for authenticated users.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Check permission
        if (!$user->can('access app commission-credits')) {
            return redirect()->route('app.history')->with('error', 'У вас нет доступа к комиссионным операциям');
        }
        
        // Build base query with proper scoping
        $baseQuery = CommissionCredit::with(['user', 'recipient', 'order']);
        
        if (!$user->hasRole('Super-Admin')) {
            $baseQuery->where(function ($query) use ($user) {
                $query->where('parent_id', $user->id)
                      ->orWhere('user_id', $user->id);
            });
        }
        
        // Get paginated records
        $commissionCredits = $baseQuery
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Calculate statistics
        $statsQuery = CommissionCredit::query();
        
        if (!$user->hasRole('Super-Admin')) {
            $statsQuery->where(function ($query) use ($user) {
                $query->where('parent_id', $user->id)
                      ->orWhere('user_id', $user->id);
            });
        }
        
        $totalAccruals = $statsQuery->clone()->where('type', 'accrual')->sum('amount');
        $totalWriteOffs = $statsQuery->clone()->where('type', 'write-off')->sum('amount');
        $totalBalance = $totalAccruals - $totalWriteOffs;
        $accrualCount = $statsQuery->clone()->where('type', 'accrual')->count();
        $writeOffCount = $statsQuery->clone()->where('type', 'write-off')->count();
        
        // Calculate pending balance for Super Admin
        $pendingBalance = 0;
        $usersWithBalance = 0;
        
        if ($user->hasRole('Super-Admin')) {
            $balances = DB::table('commission_credits')
                ->select('parent_id')
                ->selectRaw('SUM(CASE WHEN type = "accrual" THEN amount ELSE -amount END) as balance')
                ->groupBy('parent_id')
                ->havingRaw('balance > 0')
                ->get();
            
            $pendingBalance = $balances->sum('balance');
            $usersWithBalance = $balances->count();
        }
        
        return Inertia::render('App/CommissionCredits/Index', [
            'commissionCredits' => $commissionCredits,
            'statistics' => [
                'totalAccruals' => $totalAccruals,
                'totalWriteOffs' => $totalWriteOffs,
                'totalBalance' => $totalBalance,
                'accrualCount' => $accrualCount,
                'writeOffCount' => $writeOffCount,
                'pendingBalance' => $pendingBalance,
                'usersWithBalance' => $usersWithBalance,
            ],
            'isSuperAdmin' => $user->hasRole('Super-Admin'),
        ]);
    }
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
            if ($order->status !== 'completed') {
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

            // Check if user has a parent
            if (!$user->parent_id) {
                Log::info("Commission credit not created - User doesn't have a parent", [
                    'order_id' => $order->id,
                    'user_id' => $user->id
                ]);
                return;
            }

            $parent = User::find($user->parent_id);
            if (!$parent) {
                Log::error("Commission credit creation failed - Parent user not found", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'parent_id' => $user->parent_id
                ]);
                return;
            }

            // Check if parent has eligible role (ROP, Operator, or Dealer-R)
            if (!($parent->hasRole('ROP') || $parent->hasRole('Operator') || $parent->hasRole('Dealer-R'))) {
                Log::info("Commission credit not created - Parent doesn't have eligible role (ROP, Operator, or Dealer-R)", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'parent_id' => $parent->id,
                    'parent_roles' => $parent->roles->pluck('name')->toArray()
                ]);
                return;
            }

            // Check if reward_fee is not zero
            if (!$parent->reward_fee || $parent->reward_fee == 0) {
                Log::info("Commission credit not created - Reward fee is zero or not set", [
                    'order_id' => $order->id,
                    'user_id' => $parent->id,
                    'parent_id' => $parent->id,
                    'reward_fee' => $parent->reward_fee
                ]);
                return;
            }

            // Check if commission credit already exists for this order
            $existingCommission = CommissionCredit::where('order_id', $order->id)
                ->where('user_id', $user->id)
                ->where('parent_id', $parent->id)
                ->first();
            
            if ($existingCommission) {
                Log::info("Commission credit already exists for order", [
                    'order_id' => $order->id,
                    'existing_commission_id' => $existingCommission->id
                ]);
                return;
            }

            // Calculate commission amount
            $commissionAmount = ($total_price * ($parent->reward_fee / 100));
            
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
                'total_price' => $total_price,
                'parent_roles' => $parent->roles->pluck('name')->toArray()
            ]);
        } catch (\Exception $e) {
            Log::error("Commission credit creation failed", [
                'order_id' => $order_id,
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
        }
    }
}

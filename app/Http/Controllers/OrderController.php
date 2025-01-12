<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderOpening;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('App/History', [
            'orders' => Order::where('user_id', auth()->id())->orderBy('created_at', 'desc')->paginate(25)
        ]);
    }
    
    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'address' => 'required|string',
            'email' => 'required|email',
            'cart_items' => 'required|array',
            'openings' => 'required|array',
            'total_price' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => auth()->id(),
                'customer_name' => $fields['name'],
                'customer_phone' => $fields['phone'],
                'customer_address' => $fields['address'],
                'customer_email' => $fields['email'],
                'total_price' => $fields['total_price'],
            ]);

            $orderItems = array();
            
            foreach ($fields['cart_items'] as $itemID => $item) {
                $orderItems[] = [
                    'item_id' => $itemID,
                    'order_id' => $order->id,
                    'quantity' => $item['quantity'],
                ];
            }

            $orderOpenings = collect($fields['openings'])->map(function ($opening) use ($order) {
                return [
                    'order_id' => $order->id,
                    'name' => '',
                    'type' => $opening['type'],
                    'doors' => $opening['doors'],
                    'width' => $opening['width'],
                    'height' => $opening['height'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            OrderItem::insert($orderItems);
            OrderOpening::insert($orderOpenings);

            DB::commit();
            
            return to_route('app.history');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Order creation failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
        }
    }
    
    private function telegramNotifier() {
        
    }
}
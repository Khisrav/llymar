<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderOpening;
use App\Services\TochkaBankService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display a listing of the authenticated user's orders.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->can('access app history')) {
            return redirect()->route('app.home');
        }
        
        $orders = Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('App/History', [
            'orders' => $orders,
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'name'        => 'string|max:255',
            'phone'       => 'string',
            'address'     => 'string',
            'email'       => 'email',
            'cart_items'  => 'required|array',
            'openings'    => 'required|array',
            'total_price' => 'required|numeric',
            'ral_code'    => 'nullable',
            'selected_factor' => 'sometimes|string',
        ]);

        try {
            // Wrap the order creation and associated items in a DB transaction.
            $order = DB::transaction(function () use ($fields) {
                $order = $this->createOrder($fields);
                $this->createOrderItems($order, $fields['cart_items']);
                $this->createOrderOpenings($order, $fields['openings']);
                return $order;
            });

            // Make bill in Tochka Bank
            try {
                $tochkaService = new TochkaBankService();
                $tochkaService->createBill($order);
            } catch (\Exception $e) {
                Log::error("Failed to create Tochka Bank bill", [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                // Optionally: continue, or return with error
            }

            // Create commission credit
            $commissionCredit = new CommissionCreditController();
            $commissionCredit->store($order->id, $order->total_price);

            return redirect()->route('app.history');
        } catch (\Exception $e) {
            Log::error("Order creation failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            return back()->withErrors([
                'error' => 'Order creation failed. Please try again later.',
            ]);
        }
    }
    
    /**
     * Destroy the specified order.
     */
    public function destroy(Request $request, $order_id)
    {
        // Find the order
        $order = Order::findOrFail($order_id);
    
        // Get the authenticated user
        $user = Auth::user();
    
        // Check if the order can be deleted
        if ($order->user_id !== $user->id || in_array($order->status, ['paid', 'sent']) || !$user->can('delete order')) {
            return response()->json([
                'message' => 'You do not have permission to delete this order.',
                'success' => false
            ], 403);
        }
    
        // Delete the order
        $order->delete();
    
        // Redirect to the order history page
        return response()->json([
            'message' => 'Order deleted successfully',
            'success' => true
        ]);
    }
    
    /**
     * Generate and download PDF for a saved order.
     */
    public static function listPDF(int $orderId)
    {
        $order = Order::with(['orderOpenings', 'orderItems.item'])
            ->findOrFail($orderId);

        $data = self::preparePdfData($order);
        $pdf = Pdf::loadView('orders.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isRemoteEnabled' => true]);

        $pdfName = "order_{$order->order_number}_" . date('Y-m-d') . ".pdf";
        // return $pdf->download($pdfName);
        return $pdf->stream($pdfName);
    }

    /**
     * Generate and download PDF for a temporary order (e.g., from a calculator).
     */
    public static function listFromCalcPDF(Request $request)
    {
        $fields = $request->validate([
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string',
            'address'     => 'required|string',
            'email'       => 'required|email',
            'cart_items'  => 'required|array',
            'openings'    => 'required|array',
            'total_price' => 'required|numeric',
            'selected_factor' => 'sometimes|string',
        ]);

        $selectedFactor = $fields['selected_factor'] ?? 'kz';

        // Create a temporary Order instance (not persisted)
        $order = new Order([
            'user_id'          => Auth::id(),
            'customer_name'    => $fields['name'],
            'customer_phone'   => $fields['phone'],
            'customer_address' => $fields['address'],
            'customer_email'   => $fields['email'],
            'total_price'      => $fields['total_price'],
        ]);

        // Filter out invalid cart items and map to order items
        $validCartItems = array_filter($fields['cart_items'], function($item, $itemID) {
            return is_numeric($itemID) && $itemID > 0 && 
                   is_array($item) && 
                   isset($item['quantity']) && 
                   $item['quantity'] > 0;
        }, ARRAY_FILTER_USE_BOTH);

        $orderItems = [];
        foreach ($validCartItems as $itemID => $item) {
            try {
                $product = Item::find($itemID);
                if (!$product) {
                    Log::warning("Item not found with ID: {$itemID}");
                    continue;
                }
                
                $orderItems[] = (object)[
                    'item_id'        => $itemID,
                    'item'           => $product,
                    'quantity'       => $item['quantity'],
                    'checked'        => $item['checked'] ?? true,
                    'itemTotalPrice' => $item['quantity'] * Item::itemPrice($itemID, $selectedFactor),
                ];
            } catch (\Exception $e) {
                Log::error("Failed to process item with ID: {$itemID}", [
                    'item_id' => $itemID,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        // Map openings.
        $orderOpenings = array_map(function ($opening) {
            return (object)[
                'type'   => $opening['type'],
                'doors'  => $opening['doors'],
                'width'  => $opening['width'],
                'height' => $opening['height'],
            ];
        }, $fields['openings']);

        $data = self::preparePdfData($order, $orderItems, $orderOpenings);
        $data['selected_factor'] = $selectedFactor; // Add factor to data
        $pdf = Pdf::loadView('orders.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isRemoteEnabled' => true]);

        $pdfName = "order_" . ($order->order_number ?? 'temp') . "_" . date('Y-m-d') . ".pdf";
        // return $pdf->download($pdfName);
        return $pdf->stream($pdfName);
    }

    /**
     * Generate and download simple list PDF for a temporary order (e.g., from a calculator).
     */
    public static function simpleListFromCalcPDF(Request $request)
    {
        $fields = $request->validate([
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string',
            'address'     => 'required|string',
            'email'       => 'required|email',
            'cart_items'  => 'required|array',
            'openings'    => 'required|array',
            'total_price' => 'required|numeric',
            'selected_factor' => 'sometimes|string',
        ]);

        $selectedFactor = $fields['selected_factor'] ?? 'kz';

        // Create a temporary Order instance (not persisted)
        $order = new Order([
            'user_id'          => Auth::id(),
            'customer_name'    => $fields['name'],
            'customer_phone'   => $fields['phone'],
            'customer_address' => $fields['address'],
            'customer_email'   => $fields['email'],
            'total_price'      => $fields['total_price'],
        ]);

        // Filter out invalid cart items and map to order items
        $validCartItems = array_filter($fields['cart_items'], function($item, $itemID) {
            return is_numeric($itemID) && $itemID > 0 && 
                   is_array($item) && 
                   isset($item['quantity']) && 
                   $item['quantity'] > 0;
        }, ARRAY_FILTER_USE_BOTH);

        $orderItems = [];
        foreach ($validCartItems as $itemID => $item) {
            try {
                $product = Item::find($itemID);
                if (!$product) {
                    Log::warning("Item not found with ID: {$itemID}");
                    continue;
                }
                
                $orderItems[] = (object)[
                    'item_id'        => $itemID,
                    'item'           => $product,
                    'quantity'       => $item['quantity'],
                    'checked'        => $item['checked'] ?? true,
                    'itemTotalPrice' => $item['quantity'] * Item::itemPrice($itemID, $selectedFactor),
                ];
            } catch (\Exception $e) {
                Log::error("Failed to process item with ID: {$itemID}", [
                    'item_id' => $itemID,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        // Map openings.
        $orderOpenings = array_map(function ($opening) {
            return (object)[
                'type'   => $opening['type'],
                'doors'  => $opening['doors'],
                'width'  => $opening['width'],
                'height' => $opening['height'],
            ];
        }, $fields['openings']);

        $data = self::preparePdfData($order, $orderItems, $orderOpenings);
        $data['selected_factor'] = $selectedFactor; // Add factor to data
        $pdf = Pdf::loadView('orders.list_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions(['isRemoteEnabled' => true]);

        $pdfName = "list_" . ($order->order_number ?? 'temp') . "_" . date('Y-m-d') . ".pdf";
        // return $pdf->download($pdfName);
        return $pdf->stream($pdfName);
    }



    /**
     * Generate and download a sketch PDF.
     */
    public static function sketchPDF(Request $request)
    {
        $request->validate([
            'openings' => 'required|array',
        ]);
        
        if ($request->saveData == true) {
            self::saveSketch($request);
        }

        $pdf = Pdf::loadView('orders.sketch_pdf', [
                'openings' => $request->openings,
            ])
            ->setPaper('a4', 'portrait')
            ->setOptions(['isRemoteEnabled' => true]);

        $pdfName = "sketch_" . date('Y-m-d') . ".pdf";
        return $pdf->download($pdfName);
    }

    /**
     * Render the sketcher page with the order and its openings.
     */
    public static function sketcherPage(int $order_id)
    {
        $user = auth()->user();
        
        //does user own this order
        $order = Order::with(['orderOpenings', 'orderItems.item.itemProperties'])
        ->findOrFail($order_id);
        
        if ((!$user->can('access app sketcher') || $order->user_id !== $user->id) && !$user->hasRole('Super-Admin')) return redirect()->route('app.history');

        $openings = $order->orderOpenings;

        $allDoorHandles = Item::where('category_id', 29)->get();
        $doorHandleOrderItems = $order->orderItems->filter(function ($orderItem) {
            return $orderItem->item->category_id == 29;
        });

        // For each door handle order item, repeat the item according to its quantity
        $orderDoorHandles = [];
        foreach ($doorHandleOrderItems as $orderItem) {
            for ($i = 0; $i < $orderItem->quantity; $i++) {
                $orderDoorHandles[] = $orderItem->item;
            }
        }
        
        return Inertia::render('App/Order/Sketcher', [
            'order' => $order,
            'openings' => $openings,
            'door_handles' => $orderDoorHandles,
            'all_door_handles' => $allDoorHandles,
        ]);
    }


    /**
     * Save sketch data for a specific order opening.
     */
    public static function saveSketch(Request $request)
    {
        $validated = $request->validate([
            'openings' => 'required|array',
            // 'openings.*.id' => 'required|integer|exists:order_openings,id',
        ]);
            
        $allowedKeys = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'i', 'door_handle_item_id'];
    
        foreach ($validated['openings'] as $openingData) {
            $orderOpening = OrderOpening::findOrFail($openingData['id']);
            foreach ($openingData as $key => $value) {
                if (in_array($key, $allowedKeys)) {
                    $v = is_array($value) ? $value[0] : $value;
                    if ('door_handle_item_id' == $key && $v < 0) {
                        $orderOpening->{$key} = null;
                        continue;
                    }
                    $orderOpening->{$key} = is_array($value) ? $value[0] : $value;
                }
            }
            $orderOpening->save();
        }
    
        // return redirect()->route('app.history');
    }


    /* ------------------------ PRIVATE METHODS ------------------------ */

    /**
     * Create and return a new Order.
     */
    private function createOrder(array $data): Order
    {
        $order = Order::create([
            'user_id'          => Auth::id(),
            'customer_name'    => $data['name'] ?? '',
            'customer_phone'   => $data['phone'] ?? '',
            'customer_address' => $data['address'] ?? '',
            'customer_email'   => $data['email'] ?? '',
            'total_price'      => $data['total_price'],
            'ral_code'         => $data['ral_code'] ?? null,
            'selected_factor'  => $data['selected_factor'] ?? 'kz',
        ]);

        $hasServiceItems = false;
        if (isset($data['cart_items'])) {
            $itemIds = array_keys($data['cart_items']);
            $serviceItems = Item::whereIn('id', $itemIds)->where('category_id', 35)->exists();
            $hasServiceItems = $serviceItems;
        }
        
        $order->order_number = ($hasServiceItems ? '4-' : '6-') . $order->id;
        $order->save();
        return $order;
    }

    /**
     * Create order items from cart data.
     */
    private function createOrderItems(Order $order, array $cartItems): void
    {
        foreach ($cartItems as $itemID => $item) {
            // Skip invalid item IDs and items with zero or invalid quantities
            if (!is_numeric($itemID) || $itemID <= 0 || 
                !is_array($item) || !isset($item['quantity']) || 
                $item['quantity'] <= 0) {
                Log::warning("Skipping invalid cart item", [
                    'item_id' => $itemID,
                    'item' => $item,
                    'order_id' => $order->id
                ]);
                continue;
            }

            // Verify the item exists in the database
            if (!Item::where('id', $itemID)->exists()) {
                Log::warning("Skipping cart item - Item not found in database", [
                    'item_id' => $itemID,
                    'order_id' => $order->id
                ]);
                continue;
            }

            OrderItem::create([
                'item_id'  => $itemID,
                'order_id' => $order->id,
                'quantity' => $item['quantity'],
                'checked'  => $item['checked'] ?? true,
            ]);
        }
    }

    /**
     * Create order openings from the provided array.
     */
    private function createOrderOpenings(Order $order, array $openings): void
    {
        $orderOpenings = array_map(function ($opening) use ($order) {
            return [
                'order_id'   => $order->id,
                'name'       => '',
                'type'       => $opening['type'],
                'doors'      => $opening['doors'],
                'width'      => $opening['width'],
                'height'     => $opening['height'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $openings);

        OrderOpening::insert($orderOpenings);
    }

    /**
     * Send a notification via Telegram for a new order.
     */
    private function notifyViaTelegram(Order $order): bool
    {
        $botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
        $chatId   = config('services.telegram.chat_id', env('TELEGRAM_CHAT_ID'));

        $message = "<b>Новый заказ №{$order->order_number}</b>\n\n" .
                   "ФИО получателя: {$order->customer_name}\n" .
                   "Телефон: <a href='tel:{$order->customer_phone}'>{$order->customer_phone}</a>\n\n";

        if ($order->comment) {
            $message .= "<u>Комментарий:</u> <i>{$order->comment}</i>\n";
        }

        $message .= "<u>Общая стоимость: </u> <code>{$order->total_price}₽</code>\n\n" .
                    "<a href='" . url("/orders/{$order->id}/invoice-pdf") . "'>Ссылка на PDF</a>";

        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $message,
                'parse_mode' => 'HTML',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Telegram notification failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Prepare data for PDF generation.
     */
    private static function preparePdfData(
            Order $order,
            ?array $orderItems = null,
            ?array $orderOpenings = null
        ): array {
        // Prepare order items.
        if (is_null($orderItems)) {
            $orderItems = $order->orderItems->map(function ($orderItem) use ($order) {
                return (object)[
                    'item_id'        => $orderItem->item_id,
                    'item'           => $orderItem->item,
                    'quantity'       => $orderItem->quantity,
                    'checked'        => $orderItem->checked ?? true, // Use stored checked status
                    'itemTotalPrice' => $orderItem->quantity * Item::itemPrice($orderItem->item_id, $order->selected_factor ?? 'kz'),
                ];
            })->toArray();
        } else {
            $orderItems = array_map(function ($item) use ($order) {
                return (object)[
                    'item_id'        => $item->item_id,
                    'item'           => $item->item,
                    'quantity'       => $item->quantity,
                    'checked'        => $item->checked ?? true,
                    'itemTotalPrice' => $item->quantity * Item::itemPrice($item->item_id, $order->selected_factor ?? 'kz'),
                ];
            }, $orderItems);
        }

        // Prepare order openings.
        if (is_null($orderOpenings)) {
            $orderOpenings = $order->orderOpenings->map(function ($opening) {
                return (object)[
                    'type'   => $opening->type,
                    'doors'  => $opening->doors,
                    'width'  => $opening->width,
                    'height' => $opening->height,
                ];
            })->toArray();
        } else {
            $orderOpenings = array_map(function ($opening) {
                return (object)[
                    'type'   => $opening->type,
                    'doors'  => $opening->doors,
                    'width'  => $opening->width,
                    'height' => $opening->height,
                ];
            }, $orderOpenings);
        }

        return [
            'order'         => $order,
            'orderItems'    => $orderItems,
            'orderOpenings' => $orderOpenings,
            'selected_factor' => $order->selected_factor ?? 'kz',
        ];
    }



    /**
     * Download bill PDF from Tochka Bank.
     */
    public function downloadBill(Order $order)
    {
        try {
            $tochkaService = new TochkaBankService();
            $pdfContent = $tochkaService->getBillPDF($order);
            
            $filename = "invoice_{$order->id}_" . now()->format('Y-m-d') . ".pdf";
            
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($pdfContent))
                ->header('Cache-Control', 'no-cache, must-revalidate');
                
        } catch (\Exception $e) {
            Log::error('Error downloading Tochka Bank bill', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'invoice_id' => $order->invoice_id
            ]);
            
            return response()->json([
                'error' => 'Failed to download bill: ' . $e->getMessage()
            ], 500);
        }
    }

}

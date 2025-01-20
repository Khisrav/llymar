<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderOpening;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('App/History', [
            'orders' => Order::where('user_id', auth()->id())->orderBy('created_at', 'desc')->paginate(10)
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
                'order_number' => '6-' . time(),
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

            $this->telegramNotifier($order);

            return to_route('app.history');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Order creation failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Order creation failed. Please try again later.']);
        }
    }

    private function telegramNotifier(Order $order)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        $message  = "<b>Новый заказ №{$order->order_number}</b>\n\n";
        $message .= "ФИО получателя: {$order->customer_name}\n";
        $message .= "Телефон: <a href='tel:{$order->customer_phone}'>{$order->customer_phone}</a>\n\n";
        $message .= $order->comment ? "<u>Комментарий:</u> <i>{$order->comment}</i>\n" : '';
        $message .= "<u>Общая стоимость: </u> <code>{$order->total_price}₽</code>\n\n";
        $message .= "<a href='" . url("/orders/" . $order->id . "/invoice-pdf") . "'>Ссылка на PDF</a>";

        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
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
    
    private function sendInvoiceToEmail($email) {
        
    }

    public static function listPDF($orderId)
    {
        $order = Order::with(['orderOpenings', 'orderItems.item'])->findOrFail($orderId);

        $pdf = Pdf::loadView('orders.pdf', ['order' => $order]);
        $pdf->setOptions(['isRemoteEnabled' => true]);
        $pdfName = "order_{$order->order_number}_" . date('Y-m-d') . ".pdf";

        return $pdf->stream($pdfName);
    }
}

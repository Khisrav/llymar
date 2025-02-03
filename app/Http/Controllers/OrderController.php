<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderOpening;
use App\Models\WarehouseRecord;
use App\Models\WholesaleFactor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
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
                'customer_name' => $fields['name'],
                'customer_phone' => $fields['phone'],
                'customer_address' => $fields['address'],
                'customer_email' => $fields['email'],
                'total_price' => $fields['total_price'],
            ]);
            
            $order->order_number = '6-' . $order->id;
            $order->save();
    
            foreach ($fields['cart_items'] as $itemID => $item) {
                OrderItem::create([
                    'item_id' => $itemID,
                    'order_id' => $order->id,
                    'quantity' => $item['quantity'],
                ]);
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

        $pdf = Pdf::loadView('orders.pdf', ['order' => $order])
                  ->setPaper('a4', 'portrait'); // Set paper size to A4

        $pdf->setOptions(['isRemoteEnabled' => true]);
        $pdfName = "order_{$order->order_number}_" . date('Y-m-d') . ".pdf";

        return $pdf->download($pdfName);
    }

    public static function commercialOfferPDF(Request $request)
    {
        // Log::info('Incoming request data:', $request->all());

        $offer = $request->validate([
            'customer' => 'required|array',
            'manufacturer' => 'required|array',
            'openings' => 'required|array',
            'additional_items' => 'required|array',
            'services' => 'array',
            'glass' => 'required|array',
            'cart_items' => 'required|array',
            'total_price' => 'required|numeric',
        ]);

        // Log::info('Validated offer data:', $offer);

        $user = auth()->user();
        $wholesaleFactor = Cache::remember('wholesale_factor_' . $user->wholesale_factor_key, 60, function () use ($user) {
            return WholesaleFactor::where('name', $user->wholesale_factor_key)->first();
        });
        
        $reductionFactors = Cache::remember('reduction_factors_' . $offer['glass']['category_id'], 60, function () use ($offer) {
            return Category::where('id', $offer['glass']['category_id'])->first()->reduction_factors;
        });

        // Generate the PDF
        $pdf = Pdf::loadView('orders.commercial_offer_pdf', ['offer' => $offer, 'wholesaleFactor' => $wholesaleFactor, 'reductionFactors' => $reductionFactors])
                  ->setPaper('a4', 'portrait'); // Set paper size to A4

        $pdf->setOptions(['isRemoteEnabled' => true]);
        $pdfName = "offer_{$request->order_number}_" . date('Y-m-d') . ".pdf";
    
        // Generate the PDF content
        $pdfContent = $pdf->output();
    
        // Return the PDF as a downloadable file
        return Response::make($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $pdfName . '"',
        ]);
    }
    
    public static function sketchPDF(Request $request)
    {
        // $order = Order::with(['orderOpenings', 'orderItems.item'])->findOrFail($orderId);

        $pdf = Pdf::loadView('orders.sketch_pdf')
                  ->setPaper('a4', 'portrait'); 

        $pdf->setOptions(['isRemoteEnabled' => true]);
        $pdfName = "sketch_" . date('Y-m-d') . ".pdf";

        return $pdf->stream($pdfName);
    }
}

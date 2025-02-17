<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderOpening;
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
    /**
     * Display a listing of the user's orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        return Inertia::render('App/History', [
            'orders' => Order::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->paginate(10),
        ]);
    }

    /**
     * Store a newly created order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'required|string',
            'address'      => 'required|string',
            'email'        => 'required|email',
            'cart_items'   => 'required|array',
            'openings'     => 'required|array',
            'total_price'  => 'required|numeric',
            'ral_code'     => 'string',
        ]);

        try {
            DB::beginTransaction();

            $order = $this->createOrder($fields);

            $this->createOrderItems($order, $fields['cart_items']);

            $this->createOrderOpenings($order, $fields['openings']);

            DB::commit();

            $this->notifyViaTelegram($order);

            return redirect()->route('app.history');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Order creation failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Order creation failed. Please try again later.']);
        }
    }

    /**
     * Generate and download PDF for an existing order.
     *
     * @param  int  $orderId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function listPDF($orderId)
    {
        $order = Order::with(['orderOpenings', 'orderItems.item'])->findOrFail($orderId);

        $data = self::preparePdfData($order);
        $pdf = Pdf::loadView('orders.pdf', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions(['isRemoteEnabled' => true]);

        $pdfName = "order_{$order->order_number}_" . date('Y-m-d') . ".pdf";

        return $pdf->download($pdfName);
    }

    /**
     * Generate and download PDF without persisting an order (e.g., from a calculator).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function listFromCalcPDF(Request $request)
    {
        $fields = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'required|string',
            'address'      => 'required|string',
            'email'        => 'required|email',
            'cart_items'   => 'required|array',
            'openings'     => 'required|array',
            'total_price'  => 'required|numeric',
        ]);

        // Create a temporary Order model instance (not persisted)
        $order = new Order([
            'user_id'           => auth()->id(),
            'customer_name'     => $fields['name'],
            'customer_phone'    => $fields['phone'],
            'customer_address'  => $fields['address'],
            'customer_email'    => $fields['email'],
            'total_price'       => $fields['total_price'],
        ]);

        // Build a mock list of order items
        $orderItems = collect($fields['cart_items'])->map(function ($item, $itemID) {
            return (object)[
                'item_id' => $itemID,
                'item'    => Item::findOrFail($itemID),
                'quantity' => $item['quantity'],
            ];
        })->toArray();

        // Build a mock list of openings
        $orderOpenings = collect($fields['openings'])->map(function ($opening) {
            return (object)[
                'type'   => $opening['type'],
                'doors'  => $opening['doors'],
                'width'  => $opening['width'],
                'height' => $opening['height'],
            ];
        })->toArray();

        $data = self::preparePdfData($order, $orderItems, $orderOpenings);

        $pdf = Pdf::loadView('orders.pdf', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions(['isRemoteEnabled' => true]);

        $pdfName = "order_" . ($order->order_number ?? 'temp') . "_" . date('Y-m-d') . ".pdf";

        return $pdf->download($pdfName);
    }

    /**
     * Generate and download a commercial offer PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function commercialOfferPDF(Request $request)
    {
        $offer = $request->validate([
            'customer'          => 'required|array',
            'manufacturer'      => 'required|array',
            'openings'          => 'required|array',
            'additional_items'  => 'required|array',
            'services'          => 'array',
            'glass'             => 'required|array',
            'cart_items'        => 'required|array',
            'total_price'       => 'required|numeric',
            'markup_percentage' => 'required|numeric',
        ]);

        $offerAdditionalsPrice = self::calculateOfferAdditionalsPrice($offer);

        $offerOpeningsPrice = $offer['total_price'] - $offerAdditionalsPrice;

        $user = auth()->user();
        $wholesaleFactor = Cache::remember("wholesale_factor_{$user->wholesale_factor_key}", 60, function () use ($user) {
            return WholesaleFactor::where('name', $user->wholesale_factor_key)->first();
        });

        $reductionFactors = Cache::remember("reduction_factors_{$offer['glass']['category_id']}", 60, function () use ($offer) {
            return Category::find($offer['glass']['category_id'])->reduction_factors;
        });

        $pdf = Pdf::loadView('orders.commercial_offer_pdf', [
            'offer'                 => $offer,
            'offer_additionals_price' => $offerAdditionalsPrice,
            'offer_openings_price'  => $offerOpeningsPrice,
            'wholesaleFactor'       => $wholesaleFactor,
            'reductionFactors'      => $reductionFactors,
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions(['isRemoteEnabled' => true]);

        $pdfName = "offer_" . ($request->order_number ?? 'temp') . "_" . date('Y-m-d') . ".pdf";
        $pdfContent = $pdf->output();

        return Response::make($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$pdfName}\"",
        ]);
    }

    /**
     * Generate and view a sketch PDF (streams it to the browser).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public static function sketchPDF(Request $request)
    {
        $request->validate([
            'openings' => 'required|array',
        ]);
    
        $pdf = Pdf::loadView('orders.sketch_pdf', [
            'openings' => $request->openings,
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions(['isRemoteEnabled' => true]);

        $pdfName = "sketch_" . date('Y-m-d') . ".pdf";

        return $pdf->stream($pdfName);
    }

    /* -------------------------------------------------------------------------
     *  PRIVATE & PROTECTED METHODS
     * ------------------------------------------------------------------------- */

    /**
     * Create and return a new Order model.
     *
     * @param  array  $data
     * @return \App\Models\Order
     */
    private function createOrder(array $data): Order
    {
        $order = Order::create([
            'user_id'           => auth()->id(),
            'customer_name'     => $data['name'],
            'customer_phone'    => $data['phone'],
            'customer_address'  => $data['address'],
            'customer_email'    => $data['email'],
            'total_price'       => $data['total_price'],
            'ral_code'          => $data['ral_code'],
        ]);

        $order->order_number = '6-' . $order->id;
        $order->save();

        return $order;
    }

    /**
     * Create order items.
     *
     * @param  \App\Models\Order  $order
     * @param  array  $cartItems
     * @return void
     */
    private function createOrderItems(Order $order, array $cartItems): void
    {
        foreach ($cartItems as $itemID => $item) {
            OrderItem::create([
                'item_id'  => $itemID,
                'order_id' => $order->id,
                'quantity' => $item['quantity'],
            ]);
        }
    }

    /**
     * Create order openings.
     *
     * @param  \App\Models\Order  $order
     * @param  array  $openings
     * @return void
     */
    private function createOrderOpenings(Order $order, array $openings): void
    {
        $orderOpenings = collect($openings)->map(function ($opening) use ($order) {
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
        })->toArray();

        OrderOpening::insert($orderOpenings);
    }

    /**
     * Send a notification via Telegram for a created order.
     *
     * @param  \App\Models\Order  $order
     * @return bool
     */
    private function notifyViaTelegram(Order $order): bool
    {
        $botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
        $chatId   = config('services.telegram.chat_id', env('TELEGRAM_CHAT_ID'));

        $message  = "<b>Новый заказ №{$order->order_number}</b>\n\n";
        $message .= "ФИО получателя: {$order->customer_name}\n";
        $message .= "Телефон: <a href='tel:{$order->customer_phone}'>{$order->customer_phone}</a>\n\n";
        if ($order->comment) {
            $message .= "<u>Комментарий:</u> <i>{$order->comment}</i>\n";
        }
        $message .= "<u>Общая стоимость: </u> <code>{$order->total_price}₽</code>\n\n";
        $message .= "<a href='" . url("/orders/" . $order->id . "/invoice-pdf") . "'>Ссылка на PDF</a>";

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
     * Prepare data for the main PDF generation.
     *
     * @param  \App\Models\Order  $order
     * @param  array|null         $orderItems
     * @param  array|null         $orderOpenings
     * @return array
     */
    private static function preparePdfData(Order $order, array $orderItems = null, array $orderOpenings = null): array
    {
        // If $orderItems is null, it means we are generating PDF for a saved order
        if (is_null($orderItems)) {
            $orderItems = $order->orderItems->map(function ($orderItem) {
                return (object) [
                    'item_id'         => $orderItem->item_id,
                    'item'            => $orderItem->item,
                    'quantity'        => $orderItem->quantity,
                    'itemTotalPrice'  => $orderItem->quantity * Item::itemPrice($orderItem->item_id),
                ];
            })->toArray();
        } else {
            // Otherwise, we're generating PDF for a temporary order
            $orderItems = collect($orderItems)->map(function ($item) {
                return (object) [
                    'item_id'         => $item->item_id,
                    'item'            => $item->item,
                    'quantity'        => $item->quantity,
                    'itemTotalPrice'  => $item->quantity * Item::itemPrice($item->item_id),
                ];
            })->toArray();
        }

        // If $orderOpenings is null, it means we are generating PDF for a saved order
        if (is_null($orderOpenings)) {
            $orderOpenings = $order->orderOpenings->map(function ($opening) {
                return (object) [
                    'type'   => $opening->type,
                    'doors'  => $opening->doors,
                    'width'  => $opening->width,
                    'height' => $opening->height,
                ];
            })->toArray();
        } else {
            // Otherwise, we're generating PDF for a temporary order
            $orderOpenings = collect($orderOpenings)->map(function ($opening) {
                return (object) [
                    'type'   => $opening->type,
                    'doors'  => $opening->doors,
                    'width'  => $opening->width,
                    'height' => $opening->height,
                ];
            })->toArray();
        }

        return [
            'order'         => $order,
            'orderItems'    => $orderItems,
            'orderOpenings' => $orderOpenings,
        ];
    }

    /**
     * Calculate the price of additional items and services for a commercial offer.
     *
     * @param  array  $offer
     * @return float
     */
    private static function calculateOfferAdditionalsPrice(array $offer): float
    {
        $offerAdditionalsPrice = 0;

        foreach ($offer['additional_items'] as $item) {
            if (isset($offer['cart_items'][$item['id']])) {
                $price = Item::itemPrice($item['id']);
                $quantity = $offer['cart_items'][$item['id']]['quantity'];
                $offerAdditionalsPrice += $price * $quantity;
            }
        }

        $services = $offer['services'] ?? [];
        foreach ($services as $service) {
            if (isset($offer['cart_items'][$service['id']])) {
                $price = Item::itemPrice($service['id']);
                $quantity = $offer['cart_items'][$service['id']]['quantity'];
                $offerAdditionalsPrice += $price * $quantity;
            }
        }

        if (isset($offer['glass']['id'], $offer['cart_items'][$offer['glass']['id']])) {
            $price = Item::itemPrice($offer['glass']['id']);
            $quantity = $offer['cart_items'][$offer['glass']['id']]['quantity'];
            $offerAdditionalsPrice += $price * $quantity;
        }

        return $offerAdditionalsPrice;
    }
}

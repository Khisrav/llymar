<?php

namespace App\Http\Controllers;

use App\Models\CommercialOffer;
use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CommercialOfferController extends Controller
{
    /**
     * Display a listing of commercial offers for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('app.home');
        }
        
        $offers = CommercialOffer::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('App/CommercialOffers', [
            'offers' => $offers,
        ]);
    }

    /**
     * Generate and download a commercial offer PDF while saving data to database.
     */
    public function generatePDF(Request $request)
    {
        $customer = $request->get('customer');
        $manufacturer = $request->get('manufacturer');
        $openings = $request->get('openings');
        $additional_items = $request->get('additional_items');
        $glass = $request->get('glass');
        $services = $request->get('services');
        $cart_items = $request->get('cart_items');
        $total_price = $request->get('total_price');
        $markup_percentage = $request->get('markup_percentage', 1.0);
        $selected_factor = $request->get('selected_factor', 'kz');

        // Save to database
        $commercialOffer = CommercialOffer::create([
            'user_id' => Auth::id(),
            'customer_name' => $customer['name'] ?? null,
            'customer_phone' => $customer['phone'] ?? null,
            'customer_address' => $customer['address'] ?? null,
            'customer_comment' => $customer['comment'] ?? null,
            'manufacturer_name' => $manufacturer['manufacturer'] ?? null,
            'manufacturer_phone' => $manufacturer['phone'] ?? null,
            'openings' => $openings,
            'additional_items' => $additional_items,
            'glass' => $glass,
            'services' => $services,
            'cart_items' => $cart_items,
            'total_price' => $total_price,
            'markup_percentage' => $markup_percentage,
            'selected_factor' => $selected_factor,
        ]);

        $offer = [
            'customer' => $customer,
            'manufacturer' => $manufacturer,
            'openings' => $openings,
            'additional_items' => $additional_items,
            'glass' => $glass,
            'services' => $services,
            'cart_items' => $cart_items,
            'total_price' => $total_price,
            'markup_percentage' => $markup_percentage,
        ];

        $pdf = Pdf::loadView('orders.commercial_offer_pdf', compact(
            'offer',
            'selected_factor'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('commercial_offer_' . $commercialOffer->id . '.pdf');
    }

    /**
     * Download a previously generated commercial offer PDF.
     */
    public function downloadPDF(CommercialOffer $commercialOffer)
    {
        // Check if user owns this commercial offer
        if ($commercialOffer->user_id !== Auth::id()) {
            abort(403);
        }

        // Ensure openings have required fields
        $openings = $commercialOffer->openings ?? [];
        foreach ($openings as $index => $opening) {
            if (!isset($opening['type'])) {
                $openings[$index]['type'] = 'center'; // Default type
            }
            if (!isset($opening['doors'])) {
                $openings[$index]['doors'] = 1; // Default doors
            }
        }

        // Ensure glass has required structure
        $glass = $commercialOffer->glass ?? [];
        if ($glass && !isset($glass['id'])) {
            $glass['id'] = null; // Add missing id field
        }

        // Ensure additional_items and services are arrays
        $additional_items = $commercialOffer->additional_items ?? [];
        $services = $commercialOffer->services ?? [];
        $cart_items = $commercialOffer->cart_items ?? [];

        $offer = [
            'customer' => [
                'name' => $commercialOffer->customer_name,
                'phone' => $commercialOffer->customer_phone,
                'address' => $commercialOffer->customer_address,
                'comment' => $commercialOffer->customer_comment,
            ],
            'manufacturer' => [
                'name' => $commercialOffer->manufacturer_name,
                'phone' => $commercialOffer->manufacturer_phone,
            ],
            'openings' => $openings,
            'additional_items' => $additional_items,
            'glass' => $glass,
            'services' => $services,
            'cart_items' => $cart_items,
            'total_price' => $commercialOffer->total_price,
            'markup_percentage' => $commercialOffer->markup_percentage * 100, // Convert back to percentage for PDF template
        ];

        $selected_factor = $commercialOffer->selected_factor;

        $pdf = Pdf::loadView('orders.commercial_offer_pdf', compact(
            'offer',
            'selected_factor'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('commercial_offer_' . $commercialOffer->id . '.pdf');
    }

    /**
     * Remove the specified commercial offer from storage.
     */
    public function destroy(CommercialOffer $commercialOffer)
    {
        // Check if user owns this commercial offer
        if ($commercialOffer->user_id !== Auth::id()) {
            abort(403);
        }

        $commercialOffer->delete();

        return response()->json(['message' => 'Commercial offer deleted successfully']);
    }

    /**
     * Generate and download a commercial offer PDF (without saving to database).
     * This method is for quick PDF generation without persistence.
     */
    public function commercialOfferPDF(Request $request)
    {
        $customer = $request->get('customer');
        $manufacturer = $request->get('manufacturer');
        $openings = $request->get('openings');
        $additional_items = $request->get('additional_items');
        $glass = $request->get('glass');
        $services = $request->get('services');
        $cart_items = $request->get('cart_items');
        $total_price = $request->get('total_price');
        $markup_percentage = $request->get('markup_percentage');
        $selected_factor = $request->get('selected_factor', 'kz');

        $offer = [
            'customer' => $customer,
            'manufacturer' => $manufacturer,
            'openings' => $openings,
            'additional_items' => $additional_items,
            'glass' => $glass,
            'services' => $services,
            'cart_items' => $cart_items,
            'total_price' => $total_price,
            'markup_percentage' => $markup_percentage,
        ];

        $pdf = Pdf::loadView('orders.commercial_offer_pdf', compact(
            'offer',
            'selected_factor'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('commercial_offer.pdf');
    }

    /**
     * Calculate the price of additional items and services for a commercial offer.
     */
    private static function calculateOfferAdditionalsPrice(array $offer): float
    {
        $offerAdditionalsPrice = 0;
        $selectedFactor = $offer['selected_factor'] ?? 'kz';

        foreach ($offer['additional_items'] as $item) {
            if (isset($offer['cart_items'][$item['id']])) {
                $price = Item::itemPrice($item['id'], $selectedFactor);
                $quantity = $offer['cart_items'][$item['id']]['quantity'];
                $offerAdditionalsPrice += $price * $quantity;
            }
        }

        foreach ($offer['services'] ?? [] as $service) {
            if (isset($offer['cart_items'][$service['id']])) {
                $price = Item::itemPrice($service['id'], $selectedFactor);
                $quantity = $offer['cart_items'][$service['id']]['quantity'];
                $offerAdditionalsPrice += $price * $quantity;
            }
        }

        if (isset($offer['glass']['id'], $offer['cart_items'][$offer['glass']['id']])) {
            $price = Item::itemPrice($offer['glass']['id'], $selectedFactor);
            $quantity = $offer['cart_items'][$offer['glass']['id']]['quantity'];
            $offerAdditionalsPrice += $price * $quantity;
        }

        return $offerAdditionalsPrice;
    }
}

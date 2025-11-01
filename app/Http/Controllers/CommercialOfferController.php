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
     * Store a new commercial offer in the database without generating PDF.
     */
    public function store(Request $request)
    {
        $customer = $request->get('customer');
        $manufacturer = $request->get('manufacturer');
        $openings = $request->get('openings');
        $additional_items = $request->get('additional_items');
        $glass = $request->get('glass');
        $ghost_glasses = $request->get('ghost_glasses', []);
        $services = $request->get('services');
        $cart_items = $request->get('cart_items');
        $total_price = $request->get('total_price');
        $markup_percentage = $request->get('markup_percentage', 1.0);
        $selected_factor = $request->get('selected_factor', 'pz');
        $file_name = $request->get('file_name');

        // Sanitize file name if provided
        if ($file_name) {
            $file_name = trim($file_name);
            $file_name = preg_replace('/\s+/', '_', $file_name);
            $file_name = preg_replace('/[^a-zA-Z0-9_\-\(\)]/', '', $file_name);
            $file_name = substr($file_name, 0, 200);
        }

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
            'ghost_glasses' => $ghost_glasses,
            'services' => $services,
            'cart_items' => $cart_items,
            'total_price' => $total_price,
            'markup_percentage' => $markup_percentage,
            'selected_factor' => $selected_factor,
            'file_name' => $file_name,
        ]);

        return response()->json([
            'message' => 'Commercial offer saved successfully',
            'id' => $commercialOffer->id,
            'commercial_offer' => $commercialOffer,
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
        $ghost_glasses = $request->get('ghost_glasses', []);
        $services = $request->get('services');
        $cart_items = $request->get('cart_items');
        $total_price = $request->get('total_price');
        $markup_percentage = $request->get('markup_percentage', 1.0);
        $selected_factor = $request->get('selected_factor', 'pz');
        $file_name = $request->get('file_name');

        // Sanitize file name if provided
        if ($file_name) {
            $file_name = trim($file_name);
            $file_name = preg_replace('/\s+/', '_', $file_name);
            $file_name = preg_replace('/[^a-zA-Z0-9_\-\(\)]/', '', $file_name);
            $file_name = substr($file_name, 0, 200);
        }

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
            'ghost_glasses' => $ghost_glasses,
            'services' => $services,
            'cart_items' => $cart_items,
            'total_price' => $total_price,
            'markup_percentage' => $markup_percentage,
            'selected_factor' => $selected_factor,
            'file_name' => $file_name,
        ]);

        $offer = [
            'customer' => $customer,
            'manufacturer' => $manufacturer,
            'openings' => $openings,
            'additional_items' => $additional_items,
            'glass' => $glass,
            'ghost_glasses' => $ghost_glasses,
            'services' => $services,
            'cart_items' => $cart_items,
            'total_price' => $total_price,
            'markup_percentage' => $markup_percentage,
        ];

        $user = Auth::user();

        $pdf = Pdf::loadView('orders.commercial_offer_pdf', compact(
            'offer',
            'selected_factor',
            'user'
        ))->setPaper('a4', 'landscape');

        // Use custom file name if set, otherwise use default
        $downloadFileName = $commercialOffer->file_name 
            ? $commercialOffer->file_name . '.pdf' 
            : 'commercial_offer_' . $commercialOffer->id . '.pdf';

        return $pdf->download($downloadFileName);
    }

    /**
     * Download a previously generated commercial offer PDF.
     */
    public function downloadPDF(CommercialOffer $commercialOffer)
    {
        $user = Auth::user();
        
        // Allow access if:
        // 1. User is Super-Admin
        // 2. User has view-any CommercialOffer permission
        // 3. User owns this commercial offer
        if (!$user->hasRole('Super-Admin') && 
            // !$user->can('viewAny', CommercialOffer::class) && 
            !$user->can('view-any CommercialOffer') && 
            $commercialOffer->user_id !== Auth::id()) {
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
        $ghost_glasses = $commercialOffer->ghost_glasses ?? [];
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
            'ghost_glasses' => $ghost_glasses,
            'services' => $services,
            'cart_items' => $cart_items,
            'total_price' => $commercialOffer->total_price,
            'markup_percentage' => $commercialOffer->markup_percentage * 100, // Convert back to percentage for PDF template
        ];

        $selected_factor = $commercialOffer->selected_factor;

        $pdf = Pdf::loadView('orders.commercial_offer_pdf', compact(
            'offer',
            'selected_factor',
            'user'
        ))->setPaper('a4', 'landscape');

        // Use custom file name if set, otherwise use default
        $fileName = $commercialOffer->file_name 
            ? $commercialOffer->file_name . '.pdf' 
            : 'commercial_offer_' . $commercialOffer->id . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Update the file name for a commercial offer.
     */
    public function updateFileName(Request $request, CommercialOffer $commercialOffer)
    {
        $user = Auth::user();
        
        // Allow update if:
        // 1. User is Super-Admin
        // 2. User has update-any CommercialOffer permission
        // 3. User owns this commercial offer
        if (!$user->hasRole('Super-Admin') && 
            !$user->can('update-any CommercialOffer') && 
            $commercialOffer->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'file_name' => 'nullable|string|max:200|regex:/^[a-zA-Z0-9_\-\s\(\)]+$/',
        ]);

        // Sanitize file name: trim, remove multiple spaces, sanitize special chars
        $fileName = $request->file_name;
        if ($fileName) {
            $fileName = trim($fileName);
            $fileName = preg_replace('/\s+/', '_', $fileName); // Replace spaces with underscores
            $fileName = preg_replace('/[^a-zA-Z0-9_\-\(\)]/', '', $fileName); // Remove any chars not in whitelist
            $fileName = substr($fileName, 0, 200); // Ensure max length
        }

        $commercialOffer->update([
            'file_name' => $fileName,
        ]);

        return response()->json([
            'message' => 'File name updated successfully',
            'file_name' => $commercialOffer->file_name,
        ]);
    }

    /**
     * Update an existing commercial offer.
     * If 'generate_pdf' is true in the request, also download the updated PDF.
     * Otherwise, just return JSON response.
     */
    public function update(Request $request, CommercialOffer $commercialOffer)
    {
        $user = Auth::user();
        
        // Allow update if:
        // 1. User is Super-Admin
        // 2. User has update-any CommercialOffer permission
        // 3. User owns this commercial offer
        if (!$user->hasRole('Super-Admin') && 
            !$user->can('update-any CommercialOffer') && 
            $commercialOffer->user_id !== Auth::id()) {
            abort(403);
        }

        $customer = $request->get('customer');
        $manufacturer = $request->get('manufacturer');
        $openings = $request->get('openings');
        $additional_items = $request->get('additional_items');
        $glass = $request->get('glass');
        $ghost_glasses = $request->get('ghost_glasses', []);
        $services = $request->get('services');
        $cart_items = $request->get('cart_items');
        $total_price = $request->get('total_price');
        $markup_percentage = $request->get('markup_percentage', 1.0);
        $selected_factor = $request->get('selected_factor', 'pz');
        $file_name = $request->get('file_name');
        $generate_pdf = $request->get('generate_pdf', true); // Default to true for backward compatibility

        // Sanitize file name if provided
        if ($file_name) {
            $file_name = trim($file_name);
            $file_name = preg_replace('/\s+/', '_', $file_name);
            $file_name = preg_replace('/[^a-zA-Z0-9_\-\(\)]/', '', $file_name);
            $file_name = substr($file_name, 0, 200);
        }

        // Update the commercial offer
        $commercialOffer->update([
            'customer_name' => $customer['name'] ?? null,
            'customer_phone' => $customer['phone'] ?? null,
            'customer_address' => $customer['address'] ?? null,
            'customer_comment' => $customer['comment'] ?? null,
            'manufacturer_name' => $manufacturer['manufacturer'] ?? null,
            'manufacturer_phone' => $manufacturer['phone'] ?? null,
            'openings' => $openings,
            'additional_items' => $additional_items,
            'glass' => $glass,
            'ghost_glasses' => $ghost_glasses,
            'services' => $services,
            'cart_items' => $cart_items,
            'total_price' => $total_price,
            'markup_percentage' => $markup_percentage,
            'selected_factor' => $selected_factor,
            'file_name' => $file_name,
        ]);

        // If not generating PDF, return JSON response
        if (!$generate_pdf) {
            return response()->json([
                'message' => 'Commercial offer updated successfully',
                'id' => $commercialOffer->id,
                'commercial_offer' => $commercialOffer,
            ]);
        }

        // Otherwise, generate and download PDF
        $offer = [
            'customer' => $customer,
            'manufacturer' => $manufacturer,
            'openings' => $openings,
            'additional_items' => $additional_items,
            'glass' => $glass,
            'ghost_glasses' => $ghost_glasses,
            'services' => $services,
            'cart_items' => $cart_items,
            'total_price' => $total_price,
            'markup_percentage' => $markup_percentage,
        ];

        $pdf = Pdf::loadView('orders.commercial_offer_pdf', compact(
            'offer',
            'selected_factor',
            'user'
        ))->setPaper('a4', 'landscape');

        // Use custom file name if set, otherwise use default
        $downloadFileName = $commercialOffer->file_name 
            ? $commercialOffer->file_name . '.pdf' 
            : 'commercial_offer_' . $commercialOffer->id . '.pdf';

        return $pdf->download($downloadFileName);
    }

    /**
     * Remove the specified commercial offer from storage.
     */
    public function destroy(CommercialOffer $commercialOffer)
    {
        $user = Auth::user();
        
        // Allow deletion if:
        // 1. User is Super-Admin
        // 2. User has delete-any CommercialOffer permission
        // 3. User owns this commercial offer
        if (!$user->hasRole('Super-Admin') && 
            // !$user->can('deleteAny', CommercialOffer::class) && 
            !$user->can('delete-any CommercialOffer') && 
            $commercialOffer->user_id !== Auth::id()) {
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
        $ghost_glasses = $request->get('ghost_glasses', []);
        $services = $request->get('services');
        $cart_items = $request->get('cart_items');
        $total_price = $request->get('total_price');
        $markup_percentage = $request->get('markup_percentage');
        $selected_factor = $request->get('selected_factor', 'pz');

        $offer = [
            'customer' => $customer,
            'manufacturer' => $manufacturer,
            'openings' => $openings,
            'additional_items' => $additional_items,
            'glass' => $glass,
            'ghost_glasses' => $ghost_glasses,
            'services' => $services,
            'cart_items' => $cart_items,
            'total_price' => $total_price,
            'markup_percentage' => $markup_percentage,
        ];

        $user = Auth::user();

        $pdf = Pdf::loadView('orders.commercial_offer_pdf', compact(
            'offer',
            'selected_factor',
            'user'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('commercial_offer.pdf');
    }

    /**
     * Calculate the price of additional items and services for a commercial offer.
     */
    private static function calculateOfferAdditionalsPrice(array $offer): float
    {
        $offerAdditionalsPrice = 0;
        $selectedFactor = $offer['selected_factor'] ?? 'pz';

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

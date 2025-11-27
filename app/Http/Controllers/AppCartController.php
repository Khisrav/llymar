<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Inertia\Inertia;
use App\Http\Controllers\AppCalculatorController;
use App\Models\Company;
use App\Models\LandingPageOption;
use App\Models\LogisticsCompany;
use App\Services\TochkaBankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AppCartController extends Controller
{
    public function index() {
        $user = Auth::user();
        if (!$user || !$user->can('access app cart')) {
            return redirect()->route('app.home');
        }
        
        // Get dealers if user has permission to select dealers
        $dealers = collect();
        
        if ($user->hasRole('Super-Admin')) {
            // Get all dealers for super admin
            $dealers = User::whereHas('roles', function($query) {
                    $query->whereIn('name', ['Dealer', 'Dealer-Ch', 'Dealer-R']);
                })
                ->select('id', 'name', 'email')
                ->get();
        } else {
            // Get only child dealers for other roles
            $dealers = User::where('parent_id', $user->id)
                ->whereHas('roles', function($query) {
                    $query->whereIn('name', ['Dealer', 'Dealer-Ch', 'Dealer-R']);
                })
                ->select('id', 'name', 'email')
                ->get();
        }
    
        
        return Inertia::render('App/Cart', [
            'items' => AppCalculatorController::getCalculatorItems(),
            'additional_items' => AppCalculatorController::getAdditionalItems(),
            'glasses' => AppCalculatorController::getGlasses(),
            'services' => AppCalculatorController::getServices(),
            'user' => $user,
            'categories' => Category::all()->toArray(),
            'dealers' => $dealers,
            'can_select_dealer' => $user->hasRole(['Super-Admin', 'Operator', 'ROP']),
        ]);
    }

    public function index_order_confirmation(Request $request) {
        $user = Auth::user();
        $logisticsCompanies = LogisticsCompany::all(['id', 'name']);
        
        // Validate incoming data from cart page
        $orderData = $request->validate([
            'cart_items' => 'required|array',
            'openings' => 'required|array',
            'total_price' => 'required|numeric',
            'ral_code' => 'nullable|string',
            'selected_factor' => 'required|string',
            'selected_dealer_id' => 'nullable|integer|exists:users,id',
        ]);
        
        // Store order data in session for GET route access
        session(['order_confirmation_data' => $orderData]);
        
        // Get dealers if user has permission to select dealers
        $dealers = collect();
        
        if ($user->hasRole('Super-Admin')) {
            // Get all dealers for super admin
            $dealers = User::whereHas('roles', function($query) {
                    $query->whereIn('name', ['Dealer', 'Dealer-Ch', 'Dealer-R']);
                })
                ->select('id', 'name', 'email')
                ->get();
        } else {
            // Get only child dealers for other roles
            $dealers = User::where('parent_id', $user->id)
                ->whereHas('roles', function($query) {
                    $query->whereIn('name', ['Dealer', 'Dealer-Ch', 'Dealer-R']);
                })
                ->select('id', 'name', 'email')
                ->get();
        }
        
        // Get user's customer companies with their bills
        $userCompanies = Company::with('companyBills')
            ->where('type', 'customer')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'short_name', 'full_name', 'is_main']);
        
        // Get pickup location info from landing page options
        $pickupAddress = LandingPageOption::getValue('address', 'г. Москва, ул. Пушкинская, д. 1');
        $pickupPhone = LandingPageOption::getValue('phone', '+7 (999) 999-99-99');
        
        // Check if cart contains montazh services from category 35
        $hasMontazhServices = false;
        if (isset($orderData['cart_items'])) {
            $itemIds = array_keys($orderData['cart_items']);
            $hasMontazhServices = Item::whereIn('id', $itemIds)->where('category_id', 35)->exists();
        }
        
        return Inertia::render('App/OrderConfirmation', [
            'items' => AppCalculatorController::getCalculatorItems(),
            'additional_items' => AppCalculatorController::getAdditionalItems(),
            'glasses' => AppCalculatorController::getGlasses(),
            'services' => AppCalculatorController::getServices(),
            'categories' => Category::all()->toArray(),
            'logistics_companies' => $logisticsCompanies,
            'dealers' => $dealers,
            'user_companies' => $userCompanies,
            'user' => $user,
            'user_default_factor' => $user->default_factor ?? 'pz',
            'pickup_address' => $pickupAddress,
            'pickup_phone' => $pickupPhone,
            // Pass the order data from cart page
            'order_data' => $orderData,
            // Flag to indicate if cart has montazh services
            'has_montazh_services' => $hasMontazhServices,
        ]);
    }

    public function show_order_confirmation() {
        $user = Auth::user();
        
        // Retrieve order data from session
        $orderData = session('order_confirmation_data');
        
        // If no data in session, redirect to cart
        if (!$orderData) {
            return redirect()->route('app.cart')->with('error', 'Пожалуйста, заполните корзину перед оформлением заказа.');
        }
        
        $logisticsCompanies = LogisticsCompany::all(['id', 'name']);
        
        // Get dealers if user has permission to select dealers
        $dealers = collect();
        
        if ($user->hasRole('Super-Admin')) {
            // Get all dealers for super admin
            $dealers = User::whereHas('roles', function($query) {
                    $query->whereIn('name', ['Dealer', 'Dealer-Ch', 'Dealer-R']);
                })
                ->select('id', 'name', 'email')
                ->get();
        } else {
            // Get only child dealers for other roles
            $dealers = User::where('parent_id', $user->id)
                ->whereHas('roles', function($query) {
                    $query->whereIn('name', ['Dealer', 'Dealer-Ch', 'Dealer-R']);
                })
                ->select('id', 'name', 'email')
                ->get();
        }
        
        // Get user's customer companies with their bills
        $userCompanies = Company::with('companyBills')
            ->where('type', 'customer')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'short_name', 'full_name', 'is_main']);
        
        // Get pickup location info from landing page options
        $pickupAddress = LandingPageOption::getValue('address', 'г. Москва, ул. Пушкинская, д. 1');
        $pickupPhone = LandingPageOption::getValue('phone', '+7 (999) 999-99-99');
        
        // Check if cart contains montazh services from category 35
        $hasMontazhServices = false;
        if (isset($orderData['cart_items'])) {
            $itemIds = array_keys($orderData['cart_items']);
            $hasMontazhServices = Item::whereIn('id', $itemIds)->where('category_id', 35)->exists();
        }
        
        return Inertia::render('App/OrderConfirmation', [
            'items' => AppCalculatorController::getCalculatorItems(),
            'additional_items' => AppCalculatorController::getAdditionalItems(),
            'glasses' => AppCalculatorController::getGlasses(),
            'services' => AppCalculatorController::getServices(),
            'categories' => Category::all()->toArray(),
            'logistics_companies' => $logisticsCompanies,
            'dealers' => $dealers,
            'user_companies' => $userCompanies,
            'user' => $user,
            'user_default_factor' => $user->default_factor ?? 'pz',
            'pickup_address' => $pickupAddress,
            'pickup_phone' => $pickupPhone,
            // Pass the order data from session
            'order_data' => $orderData,
            // Flag to indicate if cart has montazh services
            'has_montazh_services' => $hasMontazhServices,
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Base validation rules
        $validationRules = [
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string',
            'address'     => 'nullable|string',
            'email'       => 'nullable|email',
            'cart_items'  => 'required|array',
            'openings'    => 'required|array',
            'total_price' => 'required|numeric',
            'ral_code'    => 'nullable|string',
            'selected_factor' => 'required|string',
            'selected_dealer_id' => 'nullable|integer|exists:users,id',
            'delivery_option' => 'required|string|in:montazh,dostavka,tk,samovivoz',
            'comment' => 'nullable|string',
        ];
        
        // Add conditional validation based on delivery option
        if ($request->delivery_option === 'tk') {
            $validationRules['logistics_company_id'] = 'required|integer|exists:logistics_companies,id';
            $validationRules['address'] = 'required|string';
        }
        
        if (in_array($request->delivery_option, ['montazh', 'dostavka'])) {
            $validationRules['address'] = 'required|string';
        }
        
        // Check if user is in G1 group (Super-Admin or Operator)
        $isG1 = $user->hasRole(['Super-Admin', 'Operator']);
        
        // Check if user is in G4 group
        $isG4 = $user->hasRole(['Super-Admin', 'Operator', 'ROP', 'Dealer', 'Manager', 'Dealer Ch', 'Dealer R']);
        
        // G1 users MUST provide company and bill
        if ($isG1) {
            $validationRules['company_id'] = 'required|integer|exists:companies,id';
            $validationRules['company_bill_id'] = 'required|integer|exists:company_bills,id';
        }
        // G4 users (excluding G1) should provide company if they have companies
        elseif ($isG4) {
            $validationRules['company_id'] = 'nullable|integer|exists:companies,id';
            $validationRules['company_bill_id'] = 'nullable|integer|exists:company_bills,id';
        }
        
        $fields = $request->validate($validationRules);
        
        // Additional validation: Ensure cart is not empty
        if (empty($fields['cart_items'])) {
            return back()->withErrors([
                'cart_items' => 'Корзина пуста. Добавьте товары для оформления заказа.',
            ]);
        }
        
        // Additional validation: Ensure company_bill belongs to selected company (if both are provided)
        if (isset($fields['company_id']) && isset($fields['company_bill_id'])) {
            $company = Company::with('companyBills')->find($fields['company_id']);
            
            // Verify the company belongs to the user
            if (!$company || $company->user_id !== $user->id) {
                return back()->withErrors([
                    'company_id' => 'Выбранная компания не принадлежит вам.',
                ]);
            }
            
            // Verify the bill belongs to the company
            $billBelongsToCompany = $company->companyBills->contains('id', $fields['company_bill_id']);
            if (!$billBelongsToCompany) {
                return back()->withErrors([
                    'company_bill_id' => 'Выбранный расчетный счет не принадлежит указанной компании.',
                ]);
            }
        }

        try {
            // Wrap the order creation and associated items in a DB transaction.
            $order = DB::transaction(function () use ($fields) {
                $order = OrderController::createOrder($fields);
                OrderController::createOrderItems($order, $fields['cart_items']);
                OrderController::createOrderOpenings($order, $fields['openings']);
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

            // Clear order confirmation data from session
            session()->forget('order_confirmation_data');

            // Redirect to the order view page with a flag to show download bill modal
            return redirect()->route('app.orders.show', ['order' => $order->id])
                ->with('order_created', true);
        } catch (\Exception $e) {
            Log::error("Order creation failed", [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            
            // Redirect back to order confirmation page with error
            return redirect()->route('app.cart.confirm-redirect')
                ->withErrors([
                    'error' => 'Не удалось создать заказ. Пожалуйста, попробуйте позже.',
                ]);
        }
    }
}

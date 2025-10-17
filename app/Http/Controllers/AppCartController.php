<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Inertia\Inertia;
use App\Models\LlymarCalculatorItem;
use App\Http\Controllers\AppCalculatorController;
use Illuminate\Support\Facades\Auth;

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
    
    // protected function getGlasses() {
    //     $glass_category_id = 1;
        
    //     return Item::where('category_id', $glass_category_id)->get()->toArray();
    // }
    
    // protected function getServices() {
    //     $service_category_id = 26;
        
    //     return Item::where('category_id', $service_category_id)->get()->toArray();
    // }
    
    // protected function getCalculatorItems() {
    //     $llymar_calculator_items = LlymarCalculatorItem::all();
        
    //     $items = Item::whereIn('id', $llymar_calculator_items->pluck('item_id'))->get();
        
    //     if ($items->isNotEmpty()) {
    //         return $items;
    //     }
        
    //     return [];
    // }
    
    // protected function getAdditionalItems() {
    //     $items = Item::where('is_for_llymar', true)->get();
        
    //     if ($items->isNotEmpty()) {
    //         return $items->groupBy('category_id')->toArray();
    //     }
        
    //     return [];
    // }
}

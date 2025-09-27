<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\LlymarCalculatorItem;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AppCalculatorController extends Controller
{
    public function index() {
        $user = auth()->user();
        
        if (!$user->can('access app calculator')) return redirect()->route('app.home');
    
        return Inertia::render('App/Calculator', [
            'items' => $this->getCalculatorItems(),
            'additional_items' => $this->getAdditionalItems(),
            'glasses' => $this->getGlasses(),
            'services' => $this->getServices(),
            'user' => $user,
            'categories' => Category::all()->toArray(),
            'ghost_handles' => $this->getGhostHandles(),
        ]);
    }
    
    public static function getGlasses() {
        $glass_category_id = 1;
        
        return Item::where('category_id', $glass_category_id)->get()->toArray();
    }
    
    public static function getServices() {
        $service_category_ids = [26, 35];
        
        return Item::whereIn('category_id', $service_category_ids)->get()->toArray();
    }
    
    public static function getCalculatorItems() {
        $llymar_calculator_items = LlymarCalculatorItem::all();
        
        $items = Item::whereIn('id', $llymar_calculator_items->pluck('item_id'))->get();
        
        if ($items->isNotEmpty()) return $items;
        
        return [];
    }
    
    public static function getAdditionalItems() {
        $items = Item::where('is_for_llymar', true)->get();
        
        if ($items->isNotEmpty()) {
            return $items->groupBy('category_id')->toArray();
        }
        
        return [];
    }
    
    public static function getGhostHandles() {
        return Item::where('category_id', 31)->get()->toArray() ?? [];
    }
}

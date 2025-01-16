<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\LlymarCalculatorItem;
use App\Models\WholesaleFactor;
use Inertia\Inertia;
use Illuminate\Http\Request;

class AppCalculatorController extends Controller
{
    public function index() {
        return Inertia::render('App/Calculator', [
            'items' => $this->getCalculatorItems(),
            'additional_items' => Item::where('is_for_llymar', true)->get()->toArray(),
            'glasses' => $this->getGlasses(),
            'services' => $this->getServices(),
            'user' => auth()->user(),
            'categories' => Category::all()->toArray(),
            'wholesale_factor' => WholesaleFactor::where('name', auth()->user()->wholesale_factor_key)->first(),
        ]);
    }
    
    protected function getGlasses() {
        $glass_category_id = 1;
        
        return Item::where('category_id', $glass_category_id)->get()->toArray();
    }
    
    protected function getServices() {
        $service_category_id = 26;
        
        return Item::where('category_id', $service_category_id)->get()->toArray();
    }
    
    protected function getCalculatorItems() {
        $llymar_calculator_items = LlymarCalculatorItem::all();
        
        $items = Item::whereIn('id', $llymar_calculator_items->pluck('item_id'))->get();
        
        if ($items->isNotEmpty()) {
            return $items;
        }
        
        return [];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\LlymarCalculatorItem;
use App\Models\WholesaleFactor;
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
            'wholesale_factor' => WholesaleFactor::where('group_name', $user->wholesale_factor_key)->first(),
            'factor_groups' => WholesaleFactor::all()->toArray(),
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
        
        if ($items->isNotEmpty()) return $items;
        
        return [];
    }
    
    protected function getAdditionalItems() {
        $items = Item::where('is_for_llymar', true)->get();
        
        if ($items->isNotEmpty()) {
            return $items->groupBy('category_id')->toArray();
        }
        
        return [];
    }
}

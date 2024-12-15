<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\LlymarCalculatorItem;
use Inertia\Inertia;
use Illuminate\Http\Request;

class AppCalculatorController extends Controller
{
    public function index() {
        return Inertia::render('App/Calculator', [
            'base_url' => config('app.url'),
            'items' => $this->getCalculatorItems(),
            'additional_items' => Item::where('is_for_llymar', true)->get()->toArray(),
        ]);
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

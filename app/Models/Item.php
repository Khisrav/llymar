<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Models\WholesaleFactor;
use App\Models\Category;

class Item extends Model
{
    protected $fillable = [
        'name', 'purchase_price', 'retail_price', 'category_id', 'img', 'unit', 'quantity_in_warehouse', 'vendor_code'
    ];

    public static function itemPrice(int $item_id): float
    {
        $item = Item::findOrFail($item_id);

        if (!$item) { return 0; }

        $user = auth()->user();

        $opt = $user->wholesale_factor_key;
        $ku = $user->reduction_factor_key;

        $wholesaleFactor = WholesaleFactor::where('name', $opt)->first();
        $wholesaleFactor = [
            'name' => $opt,
            'value' => $wholesaleFactor['value'] ?? 1,
        ];

        $reductionFactors = Category::where('id', $item->category_id)->first()->reduction_factors;

        if ($reductionFactors) {
            $reductionFactor = floatval(collect($reductionFactors)->firstWhere('key', $ku)['value']);
            $reductionFactor = $reductionFactor ? $reductionFactor : 1;
        } else {
            $reductionFactor = 1;
        }

        Log::info('Item ID: ' . $item_id);
        Log::info('Purchase Price: ' . $item->purchase_price);
        Log::info('Wholesale Factor: ' . $wholesaleFactor['value']);
        Log::info('Reduction Factor: ' . $reductionFactor);

        return $item->purchase_price * $wholesaleFactor['value'] * $reductionFactor;
    }
}

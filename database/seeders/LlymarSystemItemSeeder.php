<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\LlymarCalculatorItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LlymarSystemItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //find items with vendor code L4.1, L4.2 and L6.1, include them in llymar_calculator_items table
        $items = Item::where('vendor_code', 'like', 'L4%')->orWhere('vendor_code', 'like', 'L6%')->get();
        foreach ($items as $item) {
            LlymarCalculatorItem::create([
                'item_id' => $item->id
            ]);
        }
    }
}

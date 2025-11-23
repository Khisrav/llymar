<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\LlymarCalculatorItem;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LlymarCalculatorItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = Item::where('category_id', 2)->get();
        
        foreach ($items as $item) {
            LlymarCalculatorItem::create([
                'item_id' => $item->id
            ]);
        }
    }
}

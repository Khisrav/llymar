<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class L4sAndL6sSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //create item with vendor code L4.1, L4.2 and L6.1, give them "test item" as a name, price 1, category 2, unit random
        $items = [
            ['vendor_code' => 'L4.1', 'name' => 'test item', 'purchase_price' => 1, 'category_id' => 2, 'unit' => 'шт.'],
            ['vendor_code' => 'L4.2', 'name' => 'test item', 'purchase_price' => 1, 'category_id' => 2, 'unit' => 'шт.'],
            ['vendor_code' => 'L6.1', 'name' => 'test item', 'purchase_price' => 1, 'category_id' => 2, 'unit' => 'шт.'],
        ];
        Item::insert($items);
    }
}

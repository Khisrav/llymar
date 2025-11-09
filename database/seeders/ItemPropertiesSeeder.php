<?php
namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemPropertiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = Item::where('category_id', 29)->get();

        foreach ($items as $item) {
            $item->itemProperties()->createMany([
                [
                    'item_id' => $item->id,
                    'name' => 'd',
                    'value' => 0
                ],
                [
                    'item_id' => $item->id,
                    'name' => 'e',
                    'value' => 35
                ],
                [
                    'item_id' => $item->id,
                    'name' => 'g',
                    'value' => 0
                ],
                [
                    'item_id' => $item->id,
                    'name' => 'i',
                    'value' => 0
                ],
                [
                    'item_id' => $item->id,
                    'name' => 'MP',
                    'value' => 0
                ]
            ]);
        }
    }
}

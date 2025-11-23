<?php
namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemProperty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddEPropertyToItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all items with category_id = 29
        $items = Item::where('category_id', 29)->get();

        foreach ($items as $item) {
            // Check if the item already has the 'e' property
            $existingProperty = ItemProperty::where('item_id', $item->id)
                ->where('name', 'e')
                ->first();

            // If the property doesn't exist, create it
            if (!$existingProperty) {
                ItemProperty::create([
                    'item_id' => $item->id,
                    'name' => 'e',
                    'value' => 35
                ]);
                
                $this->command->info("Added 'e' property to item ID: {$item->id}");
            }
        }
        
        $this->command->info("Finished adding 'e' property to items with category_id = 29");
    }
}


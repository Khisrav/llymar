<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemProperty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HandlesItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvData = [
            ["Название" => "Руччка LLymar 900", "MP" => 900, "g" => 55, "i" => 550, "d" => 12],
            ["Название" => "Руччка Llymar 160", "MP" => 160, "g" => 55, "i" => 920, "d" => 10],
            ["Название" => "Руччка LLymar 275", "MP" => 275, "g" => 55, "i" => 863, "d" => 12],
            ["Название" => "Руччка LLymar 300", "MP" => 300, "g" => 55, "i" => 850, "d" => 12],
            ["Название" => "Руччка LLymar 305", "MP" => 305, "g" => 55, "i" => 848, "d" => 12],
            ["Название" => "Руччка LLymar 400", "MP" => 400, "g" => 55, "i" => 800, "d" => 12],
            ["Название" => "Руччка LLymar 425", "MP" => 425, "g" => 55, "i" => 788, "d" => 12],
            ["Название" => "Руччка LLymar 450", "MP" => 450, "g" => 55, "i" => 775, "d" => 12],
            ["Название" => "Руччка LLymar 600", "MP" => 600, "g" => 55, "i" => 700, "d" => 12],
            ["Название" => "Руччка LLymar 625", "MP" => 625, "g" => 55, "i" => 688, "d" => 12],
            ["Название" => "Руччка LLymar 800", "MP" => 800, "g" => 55, "i" => 600, "d" => 12],
            ["Название" => "Руччка LLymar 825", "MP" => 825, "g" => 55, "i" => 588, "d" => 12],
            ["Название" => "Руччка LLymar 900", "MP" => 900, "g" => 55, "i" => 550, "d" => 12],
            ["Название" => "Руччка LLymar 1000", "MP" => 1000, "g" => 55, "i" => 500, "d" => 12],
            ["Название" => "Руччка LLymar 1020", "MP" => 1020, "g" => 55, "i" => 490, "d" => 12],
            ["Название" => "Руччка LLymar 1200", "MP" => 1200, "g" => 55, "i" => 400, "d" => 12],
            ["Название" => "Руччка LLymar 1500", "MP" => 1500, "g" => 55, "i" => 250, "d" => 12],
            ["Название" => "Руччка LLymar 1800", "MP" => 1800, "g" => 55, "i" => 100, "d" => 12],
        ];

        $propertyColumns = ['MP', 'g', 'i', 'd'];

        DB::beginTransaction();

        try {
            foreach ($csvData as $row) {
                // Create the item
                $item = Item::create([
                    'name' => $row['Название'],
                    'category_id' => 31,
                    'purchase_price' => 0,
                    'unit' => 'шт.'
                ]);

                foreach ($propertyColumns as $columnName) {
                    if (isset($row[$columnName])) {
                        ItemProperty::create([
                            'item_id' => $item->id,
                            'name' => $columnName,
                            'value' => (string) $row[$columnName]
                        ]);
                    }
                }

                $this->command->info("Created item: {$item->name} (ID: {$item->id}) with properties");
            }

            DB::commit();
            $this->command->info("Successfully seeded " . count($csvData) . " items with their properties!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error seeding items: " . $e->getMessage());
            throw $e;
        }
    }
}

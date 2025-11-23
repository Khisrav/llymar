<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SyncPZPricesWithPurchasePriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = Item::all();
        foreach ($items as $item) {
            $item->pz = $item->purchase_price;

            if ($item->p1 == 0) {
                $item->p1 = $item->purchase_price;
            }

            if ($item->p2 == 0) {
                $item->p2 = $item->purchase_price;
            }

            if ($item->p3 == 0) {
                $item->p3 = $item->purchase_price;
            }

            if ($item->pr == 0) {
                $item->pr = $item->purchase_price;
            }

            $item->save();
        }
        Log::info('Synced ' . $items->count() . ' items');
    }
}

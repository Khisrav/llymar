<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemsAbbreviationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $abbr = [
            287 => 'М1/10', 
            288 => 'ТС/10', 
            289 => 'ТБ/10', 
            290 => 'ОС/10',
            291 => 'МА/10',
            
            419 => 'СКБ/ХР',
            427 => 'КНБ/ХР',
            428 => 'КУП',
            440 => 'РУЧ/LMR',
        ];

        foreach ($abbr as $id => $abbreviation) {
            Item::where('id', $id)->update(['abbreviation' => $abbreviation]);
        }
    }
}

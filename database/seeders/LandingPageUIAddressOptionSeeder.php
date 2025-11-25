<?php

namespace Database\Seeders;

use App\Models\LandingPageOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LandingPageUIAddressOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $option = [
            'key' => 'ui_address',
            'label' => 'Адрес для отображения',
            'value' => 'г. Краснодар, ул. Уральская, 145/3',
            'type' => 'text',
            'description' => 'Адрес для отображения на главной странице',
            'group' => 'contact',
            'order' => 5,
        ];
        LandingPageOption::updateOrCreate(['key' => $option['key']], $option);
    }
}

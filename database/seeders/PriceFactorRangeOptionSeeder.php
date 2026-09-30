<?php

namespace Database\Seeders;

use App\Models\LandingPageOption;
use Illuminate\Database\Seeder;

class PriceFactorRangeOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            [
                'key' => 'factor_p2_from',
                'label' => 'Р2 от, ₽',
                'value' => '200000',
                'type' => 'number',
                'description' => 'Сумма состава системы по Р3, начиная с которой применяется Р2. Ниже этого значения — Р3.',
                'group' => 'calculator',
                'order' => 1,
            ],
            [
                'key' => 'factor_p1_from',
                'label' => 'Р1 от, ₽',
                'value' => '500000',
                'type' => 'number',
                'description' => 'Сумма состава системы по Р3, начиная с которой применяется Р1. Должно быть больше порога Р2.',
                'group' => 'calculator',
                'order' => 2,
            ],
        ];

        foreach ($options as $option) {
            LandingPageOption::updateOrCreate(['key' => $option['key']], $option);
        }
    }
}

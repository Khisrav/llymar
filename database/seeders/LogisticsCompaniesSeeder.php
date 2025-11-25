<?php

namespace Database\Seeders;

use App\Models\LogisticsCompany;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LogisticsCompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logisticsCompanies = [
            'Деловые линии',
            'Boxberry',
            'СДЭК',
            'Почта России',
            'Яндекс.Доставка',
        ];

        foreach ($logisticsCompanies as $logisticsCompany) {
            LogisticsCompany::create([
                'name' => $logisticsCompany,
            ]);
        }
    }
}

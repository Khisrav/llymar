<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parties = array(
            array('id' => '1','idAdmin' => '6000','name' => 'Стекло'),
            array('id' => '2','idAdmin' => '6000','name' => 'Разное'),
            array('id' => '3','idAdmin' => '6000','name' => 'Раздвижные системы'),
            array('id' => '4','idAdmin' => '6000','name' => 'Штанги 30x10 мм'),
            array('id' => '5','idAdmin' => '6000','name' => 'Уплотнители'),
            array('id' => '6','idAdmin' => '6000','name' => 'П-профиль'),
            array('id' => '7','idAdmin' => '6000','name' => 'Петли ЕВРОПА'),
            array('id' => '8','idAdmin' => '6000','name' => 'Ручки-кнобы'),
            array('id' => '9','idAdmin' => '6000','name' => 'Ручки-скобы'),
            array('id' => '10','idAdmin' => '6000','name' => 'Штанги 19мм'),
            array('id' => '11','idAdmin' => '6000','name' => 'Крепление штанги 19 мм'),
            array('id' => '12','idAdmin' => '6000','name' => 'Крепление Штанги 30*10 мм'),
            array('id' => '13','idAdmin' => '6000','name' => 'Конекторы'),
            array('id' => '14','idAdmin' => '6000','name' => 'Стекло обработка'),
            array('id' => '15','idAdmin' => '6000','name' => 'Другое'),
            array('id' => '16','idAdmin' => '6000','name' => 'Угловой стабилизатор'),
            array('id' => '17','idAdmin' => '6000','name' => 'Петли МАЙЯ'),
            array('id' => '18','idAdmin' => '6000','name' => 'Ручки-купе'),
            array('id' => '19','idAdmin' => '6000','name' => 'УПАКОВКА'),
            array('id' => '20','idAdmin' => '6000','name' => 'Межкомнатные перегородки'),
            array('id' => '21','idAdmin' => '6000','name' => 'Штанга 15*15'),
            array('id' => '22','idAdmin' => '6000','name' => 'Обвязка 15*15'),
            array('id' => '23','idAdmin' => '6000','name' => 'Коммерческие ограждения'),
            array('id' => '24','idAdmin' => '6000','name' => 'Крючки'),
            array('id' => '25','idAdmin' => '6000','name' => 'Монтаж'),
            array('id' => '26','idAdmin' => '6000','name' => 'Услуги'),
        );
        
        $formatted_categories = array();
        foreach ($parties as $party) {
            Log::info($party);
            $formatted_categories[] = [
                'id' => $party['id'],
                // 'idAdmin' => $party['idAdmin'],
                'name' => $party['name'],
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ];
        }
        
        DB::table('categories')->insert($formatted_categories);
    }
}

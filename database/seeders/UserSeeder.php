<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Худоёров Хисрав',
                'email' => 'kh.khisrav2018@gmail.com',
                'discount' => 0,
            ],
            [
                'name' => 'Полстяной Александр',
                'email' => 'alive250787@gmail.com',
                'discount' => 0,
            ],
        ];
        
        DB::table('users')->insert($users);
    }
}
    
<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateNewDealerRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Dealer-Ch',
                'display_name' => 'Дилер Ч',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Dealer-R',
                'display_name' => 'Дилер Р',
                'guard_name' => 'web',
            ],
        ];
        
        foreach ($roles as $role) {
            Role::create($role);
        }
        
    }
}

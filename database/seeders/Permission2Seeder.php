<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Permission2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'can access app wholesale-factors'
        ];
        
        foreach ($permissions as $permissionName) {
            Permission::create(['name' => $permissionName]);
        }
        
        //give permissions to admin and operator
        $adminRole = Role::where('name', 'Super-Admin')->first();
        $operatorRole = Role::where('name', 'Operator')->first();
        
        $adminRole->givePermissionTo($permissions);
        $operatorRole->givePermissionTo($permissions);
    }
}

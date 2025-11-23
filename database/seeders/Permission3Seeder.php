<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Permission3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create([
            'name' => 'can access dxf',
            'guard_name' => 'web'
        ]);
        
        // Uncomment if you want to assign to roles
        // $superAdminRole = Role::where('name', 'Super-Admin')->where('guard_name', 'web')->first();
        // $operatorRole = Role::where('name', 'Operator')->where('guard_name', 'web')->first();
        
        // if ($superAdminRole) {
        //     $superAdminRole->givePermissionTo('can access dxf');
        // }
        // if ($operatorRole) {
        //     $operatorRole->givePermissionTo('can access dxf');
        // }
    }
}

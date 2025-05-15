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
        Permission::create(['name' => 'can access dxf']);
        // $superAdminRole = Role::where('name', 'Super-Admin')->first();
        // $operatorRole = Role::where('name', 'Operator')->first();
        
        // $superAdminRole->givePermissionTo('can access dxf');
        // $operatorRole->givePermissionTo('can access dxf');
    }
}

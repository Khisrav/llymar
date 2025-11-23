<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Clear existing permissions
        Permission::query()->delete();
        Role::query()->delete();

        // Create new permissions (explicitly for web guard)
        $permissions = [
            'access admin panel',
            'access app calculator',
            'access app history',
            'access app cart',
            
            'view-any order',
            'view order',
            'create order',
            'update order',
            'delete order',
            'appoint order manager',
            
            'view-any user',
            'view user',
            'create user',
            // 'create user operator',
            // 'create user manager',
            // 'create user dealer',
            // 'create user workman',
            'update user',
            'delete user',
            
            'view-any category',
            'view category',
            'create category',
            'update category',
            'delete category',
            
            'view-any item',
            'view item',
            'create item',
            'update item',
            'delete item',
            
            'view-any warehouse-record',
            'view warehouse-record',
            'create warehouse-record',
            'update warehouse-record',
            'delete warehouse-record',
        ];

        foreach ($permissions as $permissionName) {
            Permission::create([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
        }

        // Create roles and assign permissions (explicitly for web guard)
        //create super admin
        $superAdmin = Role::create([
            'name' => 'Super-Admin',
            'guard_name' => 'web'
        ]);
        $superAdmin->givePermissionTo($permissions);
        
        $operator = Role::create([
            'name' => 'Operator',
            'guard_name' => 'web'
        ]);
        $operator->givePermissionTo([
            'access admin panel',
            'access app calculator',
            
            'view-any category',
            'view category',
            'create category',
            'update category',
            'delete category',
            
            'view-any item',
            'view item',
            'create item',
            'update item',
            'delete item',
            
            'view-any order',
            'view order',
            'create order',
            'update order',
            // 'update order status',
            'delete order',
            'appoint order manager',
            
            
            'view-any user',
            'view user',
            'create user',
            // 'create user manager',
            'update user',
            'delete user',
            
            'view-any warehouse-record',
            'view warehouse-record',
            'create warehouse-record',
            'update warehouse-record',
            'delete warehouse-record',
        ]);
        
        $manager = Role::create([
            'name' => 'Manager',
            'guard_name' => 'web'
        ]);
        $manager->givePermissionTo([
            'access admin panel',
            'access app calculator',
            'view-any order',
            'view order',
            // 'update order status',
            
            'view-any user',
            'view user',
            'create user',
            // 'create user manager',
            'update user',
            'delete user',
        ]);
        
        $agent = Role::create([
            'name' => 'Agent',
            'guard_name' => 'web'
        ]);
        $agent->givePermissionTo([
            'access admin panel',
            'access app calculator',
            'view-any order',
            'view order',
            // 'update order status',
            
            'view-any user',
            'view user',
            'create user',
            // 'create user manager',
            'update user',
            'delete user',
        ]);
        
        $dealer = Role::create([
            'name' => 'Dealer',
            'guard_name' => 'web'
        ]);
        $dealer->givePermissionTo([
            'access app calculator',
            'access app history',
            'access app cart',
        ]);
        
        $workman = Role::create([
            'name' => 'Workman',
            'guard_name' => 'web'
        ]);
        $workman->givePermissionTo([
            'access admin panel',
            'view-any order',
            'view order',
            // 'update order', // means he should be able to change status and nothing else
        ]);
        
        //user with id 1 should be super admin
        $user = User::find(1);
        if ($user) {
            $user->assignRole($superAdmin);
        }
    }
}

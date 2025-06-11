<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class ResetPermissionsWebOnly extends Command
{
    protected $signature = 'permissions:reset-web-only {--force : Skip confirmation}';
    protected $description = 'Clear all permissions and recreate them only for web guard';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will delete ALL permissions and roles. Are you sure?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Clearing permission cache...');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('Deleting all existing permissions and roles...');
        
        // Delete all permissions and roles
        Permission::query()->delete();
        Role::query()->delete();

        $this->info('Creating permissions for web guard only...');

        // Define all permissions that should exist for web guard
        $permissions = [
            'access admin panel',
            'access app calculator',
            'access app history',
            'access app cart',
            'can access app wholesale-factors',
            'can access dxf',
            
            'view-any order',
            'view order',
            'create order',
            'update order',
            'delete order',
            'appoint order manager',
            
            'view-any user',
            'view user',
            'create user',
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

        // Create permissions only for web guard
        foreach ($permissions as $permissionName) {
            Permission::create([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
            $this->info("Created permission: {$permissionName} (web guard)");
        }

        $this->info('Creating roles...');

        // Create roles for web guard
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
            'delete order',
            'appoint order manager',
            
            'view-any user',
            'view user',
            'create user',
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
            
            'view-any user',
            'view user',
            'create user',
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
            
            'view-any user',
            'view user',
            'create user',
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
        ]);

        // Assign Super-Admin role to user with ID 1 if they exist
        $user = User::find(1);
        if ($user) {
            $user->assignRole($superAdmin);
            $this->info('Assigned Super-Admin role to user ID 1');
        }

        // Clear cache again
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('✅ Successfully reset permissions for web guard only!');
        $this->info('Total permissions created: ' . count($permissions));
        $this->info('Total roles created: 5');

        return 0;
    }
} 
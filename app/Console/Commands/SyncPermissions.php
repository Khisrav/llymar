<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SyncPermissions extends Command
{
    protected $signature = 'command-permissions:sync';
    protected $description = 'Sync permissions to match what policies expect';

    public function handle()
    {
        $this->info('Syncing permissions...');
        
        // Clear permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Define the standard permissions that should exist based on your policies
        $expectedPermissions = [
            // Admin panel access
            'access admin panel',
            'access app calculator',
            'access app history',
            'access app cart',
            'access app wholesale-factors',
            'access dxf',
            
            // Order permissions
            // 'view-any order',
            // 'view order',
            // 'create order',
            // 'update order',
            // 'delete order',
            // 'appoint order manager',
            
            // // User permissions
            // 'view-any user',
            // 'view user',
            // 'create user',
            // 'update user',
            // 'delete user',
            
            // // Category permissions (lowercase to match seeder)
            // 'view-any category',
            // 'view category',
            // 'create category',
            // 'update category',
            // 'delete category',
            
            // // Item permissions
            // 'view-any item',
            // 'view item',
            // 'create item',
            // 'update item',
            // 'delete item',
            
            // // Warehouse Record permissions
            // 'view-any warehouse-record',
            // 'view warehouse-record',
            // 'create warehouse-record',
            // 'update warehouse-record',
            // 'delete warehouse-record',
            
            // // Policy-based permissions (if they're needed)
            // 'view-any Permission',
            // 'view Permission',
            // 'create Permission',
            // 'update Permission',
            // 'delete Permission',
            // 'delete-any Permission',
            // 'restore Permission',
            // 'restore-any Permission',
            // 'replicate Permission',
            // 'reorder Permission',
            // 'force-delete Permission',
            // 'force-delete-any Permission',
            
            // 'view-any Role',
            // 'view Role',
            // 'create Role',
            // 'update Role',
            // 'delete Role',
            // 'delete-any Role',
            // 'restore Role',
            // 'restore-any Role',
            // 'replicate Role',
            // 'reorder Role',
            // 'force-delete Role',
            // 'force-delete-any Role',
        ];
        
        $created = 0;
        
        foreach ($expectedPermissions as $permissionName) {
            if (!Permission::where('name', $permissionName)->exists()) {
                Permission::create(['name' => $permissionName]);
                $this->info("Created permission: {$permissionName}");
                $created++;
            }
        }
        
        if ($created === 0) {
            $this->info('All expected permissions already exist.');
        } else {
            $this->info("Created {$created} new permissions.");
        }
        
        // Clear cache again
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->info('Permission sync completed!');
        
        return 0;
    }
} 
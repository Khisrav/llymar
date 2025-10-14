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
        
        /*
        super-admin - супермен
        operator - оператор
        rop - РОП (руководитель отдела продаж)
        dealer - дилер
        dealer-ch - дилер Ч
        dealer-r - дилер Р
        manager - менеджер
        workman - цеховщик
        */

        // Define the standard permissions that should exist based on your policies
        $expectedPermissions = [
            // Admin panel access
            'access admin panel',
            'access app calculator',
            'access app history',
            'access app cart',
            'access app wholesale-factors',
            'access app users',
            'access dxf',

            //roles management access
            'view super-admin',
            'view-any super-admin',
            'create super-admin',
            'update super-admin',
            'delete super-admin',

            'view operator',
            'view-any operator',
            'create operator',
            'update operator',
            'delete operator',

            'view rop',
            'view-any rop',
            'create rop',
            'update rop',
            'delete rop',

            'view dealer',
            'view-any dealer',
            'create dealer',
            'update dealer',
            'delete dealer',

            'view dealer-ch',
            'view-any dealer-ch',
            'create dealer-ch',
            'update dealer-ch',
            'delete dealer-ch',

            'view dealer-r',
            'view-any dealer-r',
            'create dealer-r',
            'update dealer-r',
            'delete dealer-r',

            'view manager',
            'view-any manager',
            'create manager',
            'update manager',
            'delete manager',

            'view workman',
            'view-any workman',
            'create workman',
            'update workman',
            'delete workman',
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
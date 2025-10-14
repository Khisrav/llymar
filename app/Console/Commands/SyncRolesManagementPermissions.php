<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncRolesManagementPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync-roles-management';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync role management permissions for each role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rolesMangementPermissions = [
            'Super-Admin' => [
                'view super-admin',
                'view-any super-admin',
                'create super-admin',
                'update super-admin',
                'delete super-admin',
            ],

            'Operator' => [
                'view operator',
                'view-any operator',
                'create operator',
                'update operator',
                'delete operator',
            ],

            'ROP' => [
                'view rop',
                'view-any rop',
                'create rop',
                'update rop',
                'delete rop',
            ],

            'Dealer' => [
                'view dealer',
                'view-any dealer',
                'create dealer',
                'update dealer',
                'delete dealer',
            ],

            'Dealer-Ch' => [
                'view dealer-ch',
                'view-any dealer-ch',
                'create dealer-ch',
                'update dealer-ch',
                'delete dealer-ch',
            ],

            'Dealer-R' => [
                'view dealer-r',
                'view-any dealer-r',
                'create dealer-r',
                'update dealer-r',
                'delete dealer-r',
            ],

            'Manager' => [
                'view manager',
                'view-any manager',
                'create manager',
                'update manager',
                'delete manager',
            ],

            'Workman' => [
                'view workman',
                'view-any workman',
                'create workman',
                'update workman',
                'delete workman',
            ],
        ];

        $rolesPermissionsMatrix = [
            'Super-Admin' => [
                $rolesMangementPermissions['Super-Admin'],
                $rolesMangementPermissions['Operator'],
                $rolesMangementPermissions['ROP'],
                $rolesMangementPermissions['Dealer'],
                $rolesMangementPermissions['Dealer-Ch'],
                $rolesMangementPermissions['Dealer-R'],
                $rolesMangementPermissions['Workman'],
            ],
            'Operator' => [
                $rolesMangementPermissions['Dealer'],
                $rolesMangementPermissions['Dealer-Ch'],
                $rolesMangementPermissions['Workman'],
            ],
            'ROP' => [
                $rolesMangementPermissions['Dealer'],
            ],
            'Dealer' => [
                $rolesMangementPermissions['Manager'],
            ],
            'Dealer-Ch' => [
                $rolesMangementPermissions['Manager'],
            ],
            'Dealer-R' => [
                $rolesMangementPermissions['Dealer'],
                $rolesMangementPermissions['Manager'],
            ],
            'Manager' => [],
            'Workman' => [],
        ];

        $this->info('Syncing role management permissions...');
        
        // Clear permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Collect all unique permissions from rolesManagementPermissions
        $allRolesManagementPermissions = collect($rolesMangementPermissions)
            ->flatten()
            ->unique()
            ->values()
            ->all();

        $this->info('Collected ' . count($allRolesManagementPermissions) . ' unique role management permissions.');

        // Process each role
        foreach ($rolesPermissionsMatrix as $roleName => $permissionGroups) {
            $role = Role::where('name', $roleName)->first();
            
            if (!$role) {
                $this->warn("Role '{$roleName}' not found. Skipping...");
                continue;
            }

            // Remove all existing permissions that are in rolesManagementPermissions
            $this->info("Processing role: {$roleName}");
            $role->revokePermissionTo($allRolesManagementPermissions);
            $this->info("  - Removed existing role management permissions");

            // Flatten the permission groups and add them to the role
            $permissionsToAdd = collect($permissionGroups)->flatten()->unique()->all();
            
            if (count($permissionsToAdd) > 0) {
                $role->givePermissionTo($permissionsToAdd);
                $this->info("  - Added " . count($permissionsToAdd) . " permissions");
            } else {
                $this->info("  - No permissions to add");
            }
        }

        // Clear cache again
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->info('Role management permissions sync completed!');
        
        return 0;
    }
}

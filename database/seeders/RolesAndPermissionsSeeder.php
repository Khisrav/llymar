<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Clear cache to avoid conflicts
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesData = [
            [
                "role" => "Super-Admin",
                "permissions" => []
            ],
            [
                "role" => "Operator",
                "permissions" => [
                    "view-any Category",
                    "view Category",
                    "create Category",
                    "update Category",
                    "delete Category",
                    "view-any Item",
                    "view Item",
                    "create Item",
                    "update Item",
                    "delete Item",
                    "view-any Order",
                    "view Order",
                    "update Order",
                    "delete Order",
                    "view-any Portfolio",
                    "view Portfolio",
                    "create Portfolio",
                    "update Portfolio",
                    "delete Portfolio",
                    "view User",
                    "create User",
                    "update User",
                    "delete User",
                    "view-any WarehouseRecord",
                    "view WarehouseRecord",
                    "create WarehouseRecord",
                    "update WarehouseRecord",
                    "delete WarehouseRecord",
                    "access admin panel",
                    "access app calculator",
                    "access app wholesale-factors"
                ]
            ],
            [
                "role" => "Manager",
                "permissions" => [
                    "access admin panel",
                    "access app calculator",
                    "view-any order",
                    "view order",
                    "view-any user",
                    "view user",
                    "create user",
                    "update user",
                    "delete user"
                ]
            ],
            [
                "role" => "Dealer",
                "permissions" => [
                    "access app calculator",
                    "access app history",
                    "access app cart"
                ]
            ],
            [
                "role" => "Workman",
                "permissions" => [
                    "access admin panel",
                    "view-any order",
                    "view order"
                ]
            ],
            [
                "role" => "ROP",
                "permissions" => [
                    "access admin panel",
                    "access app calculator",
                    "view-any order",
                    "view order",
                    "view-any user",
                    "view user",
                    "create user",
                    "update user",
                    "delete user"
                ]
            ]
        ];

        foreach ($rolesData as $item) {
            // Create or get the role with guard_name = web
            $role = Role::firstOrCreate(
                ['name' => $item['role'], 'guard_name' => 'web']
            );

            // Create permissions with guard_name = web
            $permissions = collect($item['permissions'])->map(function ($perm) {
                return Permission::firstOrCreate(
                    ['name' => $perm, 'guard_name' => 'web']
                );
            });

            // Assign permissions to the role
            $role->syncPermissions($permissions);
        }

        $this->command->info('Roles and permissions (web guard) seeded successfully.');
    }
}

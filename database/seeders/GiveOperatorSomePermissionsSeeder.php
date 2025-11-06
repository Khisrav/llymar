<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GiveOperatorSomePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {        
        // Define the resources and their permissions
        $resources = [
            'Company',
            'LogisticsCompany',
            'Portfolio',
            'News',
            'LandingPageOption',
            'HeroCarousel',
        ];
        
        $permissionTypes = [
            'view-any',
            'view',
            'create',
            'update',
            'delete',
        ];
        
        // Create permissions if they don't exist
        $permissionsToAssign = [];
        foreach ($resources as $resource) {
            foreach ($permissionTypes as $type) {
                $permissionName = "{$type} {$resource}";
                
                // Create permission if it doesn't exist
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
                
                $permissionsToAssign[] = $permissionName;
            }
        }
        
        // Get the Operator role
        $operatorRole = Role::where('name', 'Operator')->first();
        
        if ($operatorRole) {
            // Give permissions to Operator role
            $operatorRole->givePermissionTo($permissionsToAssign);
            $this->command->info('Operator role permissions updated successfully!');
        } else {
            $this->command->error('Operator role not found!');
        }
    }
}

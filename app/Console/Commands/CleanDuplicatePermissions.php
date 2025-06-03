<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CleanDuplicatePermissions extends Command
{
    protected $signature = 'permissions:clean-duplicates';
    protected $description = 'Clean duplicate permissions and fix case sensitivity issues';

    public function handle()
    {
        $this->info('Starting permission cleanup...');
        
        // Clear permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Get all permissions grouped by similar names (case-insensitive)
        $permissions = Permission::all();
        $grouped = $permissions->groupBy(function ($permission) {
            return strtolower(trim($permission->name));
        });
        
        $duplicateCount = 0;
        
        foreach ($grouped as $normalizedName => $permissionGroup) {
            if ($permissionGroup->count() > 1) {
                $this->info("Found duplicates for: {$normalizedName}");
                
                // Keep the first one, delete the rest
                $keepPermission = $permissionGroup->first();
                $duplicates = $permissionGroup->skip(1);
                
                foreach ($duplicates as $duplicate) {
                    $this->warn("  Deleting duplicate: {$duplicate->name} (ID: {$duplicate->id})");
                    
                    // Move role associations to the kept permission
                    $roles = $duplicate->roles()->get();
                    foreach ($roles as $role) {
                        if (!$keepPermission->roles()->where('id', $role->id)->exists()) {
                            $keepPermission->roles()->attach($role->id);
                        }
                        $duplicate->roles()->detach($role->id);
                    }
                    
                    // Move user associations to the kept permission
                    $users = $duplicate->users()->get();
                    foreach ($users as $user) {
                        if (!$keepPermission->users()->where('id', $user->id)->exists()) {
                            $keepPermission->users()->attach($user->id);
                        }
                        $duplicate->users()->detach($user->id);
                    }
                    
                    $duplicate->delete();
                    $duplicateCount++;
                }
            }
        }
        
        $this->info("Cleaned up {$duplicateCount} duplicate permissions.");
        
        // Clear cache again after cleanup
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->info('Permission cleanup completed!');
        
        return 0;
    }
} 
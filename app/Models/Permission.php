<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Permission extends SpatiePermission
{
    protected $fillable = ['name', 'display_name', 'guard_name'];

    public function getDisplayNameAttribute()
    {
        return $this->attributes['display_name'] ?? $this->name;
    }
    
    /**
     * Override the delete method to handle relationship cleanup
     */
    public function delete()
    {
        // Clear cache before deleting
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Clean up related records safely
        try {
            // Remove permission from users
            DB::table('model_has_permissions')
                ->where('permission_id', $this->id)
                ->delete();
            
            // Remove permission from roles
            DB::table('role_has_permissions')
                ->where('permission_id', $this->id)
                ->delete();
            
            // Now safe to delete the permission
            return parent::delete();
        } catch (\Exception $e) {
            Log::error('Error deleting permission: ' . $e->getMessage());
            throw $e;
        }
    }
}

<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RolePermissionMatrix extends Page
{
    protected static string $resource = RoleResource::class;

    protected static string $view = 'filament.resources.role-resource.pages.role-permission-matrix';

    protected static ?string $title = 'Матрица ролей и разрешений';

    protected static ?string $navigationLabel = 'Матрица разрешений';

    public array $rolePermissions = [];
    public array $roles = [];
    public array $permissions = [];
    public array $groupedPermissions = [];
    public int $perPage = 20;
    public int $currentPage = 1;
    public int $totalPages = 1;
    public int $totalPermissions = 0;
    public string $search = '';
    public string $filterModel = '';
    public string $filterAction = '';
    public array $availableModels = [];
    public array $availableActions = [];

    public function mount(): void
    {
        Log::info('RolePermissionMatrix: mount() called', [
            'current_page' => $this->currentPage,
            'per_page' => $this->perPage,
            'search' => $this->search,
            'filter_model' => $this->filterModel,
            'filter_action' => $this->filterAction
        ]);

        // Load roles and convert to array
        $this->roles = Role::where('guard_name', 'web')
            ->orderBy('id')
            ->get()
            ->toArray();
        
        Log::info('RolePermissionMatrix: Roles loaded', [
            'roles_count' => count($this->roles),
            'role_ids' => array_column($this->roles, 'id')
        ]);
        
        // Extract available models and actions from all permissions (for filter dropdowns)
        $this->extractFiltersFromPermissions();
        
        // Load all permissions and apply search and filters
        $query = Permission::where('guard_name', 'web');
        
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('display_name', 'like', '%' . $this->search . '%');
            });
        }
        
        // Apply model filter
        if (!empty($this->filterModel)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '% ' . $this->filterModel)
                  ->orWhere('name', 'like', '%-' . $this->filterModel)
                  ->orWhere('name', 'like', '%.' . $this->filterModel)
                  ->orWhere('name', 'like', '%::' . $this->filterModel);
            });
        }
        
        // Apply action filter
        if (!empty($this->filterAction)) {
            $query->where('name', 'like', $this->filterAction . '%');
        }
        
        $allPermissions = $query->get();
        
        // Sort permissions with custom logic
        $sortedPermissions = $allPermissions->sort(function ($a, $b) {
            // Define special prefixes that should be at the very bottom
            $specialPrefixes = ['reorder', 'force-delete', 'replicate', 'restore'];
            
            $aIsTranslated = $a->display_name !== $a->name;
            $bIsTranslated = $b->display_name !== $b->name;
            
            $aHasSpecialPrefix = false;
            $bHasSpecialPrefix = false;
            
            // Check if permissions have special prefixes
            foreach ($specialPrefixes as $prefix) {
                if (str_starts_with($a->name, $prefix)) {
                    $aHasSpecialPrefix = true;
                }
                if (str_starts_with($b->name, $prefix)) {
                    $bHasSpecialPrefix = true;
                }
            }
            
            // Priority 1: Translated permissions (display_name != name) at the top
            if ($aIsTranslated && !$bIsTranslated) return -1;
            if (!$aIsTranslated && $bIsTranslated) return 1;
            
            // Priority 2: Among non-translated permissions, special prefixes at the bottom
            if (!$aIsTranslated && !$bIsTranslated) {
                if (!$aHasSpecialPrefix && $bHasSpecialPrefix) return -1;
                if ($aHasSpecialPrefix && !$bHasSpecialPrefix) return 1;
            }
            
            // Priority 3: Sort by name alphabetically
            return strcmp($a->name, $b->name);
        });
        
        $this->permissions = $sortedPermissions->values()->toArray();
        $this->totalPermissions = count($this->permissions);
        $this->totalPages = (int) ceil($this->totalPermissions / $this->perPage);

        Log::info('RolePermissionMatrix: Permissions sorted', [
            'total_permissions' => $this->totalPermissions,
            'total_pages' => $this->totalPages,
            'current_page' => $this->currentPage
        ]);

        // Get paginated permissions
        $paginatedPermissions = array_slice(
            $this->permissions, 
            ($this->currentPage - 1) * $this->perPage, 
            $this->perPage
        );

        Log::info('RolePermissionMatrix: Permissions paginated', [
            'paginated_count' => count($paginatedPermissions),
            'permission_ids' => array_column($paginatedPermissions, 'id')
        ]);

        // Group paginated permissions by prefix
        $grouped = collect($paginatedPermissions)->groupBy(function ($permission) {
            $parts = explode('.', $permission['name']);
            return count($parts) > 1 ? $parts[0] : 'other';
        });

        // Convert grouped permissions to array
        $this->groupedPermissions = $grouped->map(function ($group) {
            return $group->toArray();
        })->toArray();

        Log::info('RolePermissionMatrix: Permissions grouped', [
            'groups_count' => count($this->groupedPermissions),
            'group_names' => array_keys($this->groupedPermissions)
        ]);

        // Load existing role-permission relationships for all permissions
        $rolesCollection = Role::where('guard_name', 'web')->orderBy('id')->get();
        $permissionsCollection = Permission::where('guard_name', 'web')->get();
        
        Log::info('RolePermissionMatrix: Loading role-permission relationships', [
            'roles_count' => $rolesCollection->count(),
            'permissions_count' => $permissionsCollection->count()
        ]);
        
        foreach ($rolesCollection as $role) {
            foreach ($permissionsCollection as $permission) {
                $this->rolePermissions[$role->id][$permission->id] = $role->hasPermissionTo($permission->name);
            }
        }

        Log::info('RolePermissionMatrix: mount() completed', [
            'rolePermissions_loaded' => count($this->rolePermissions)
        ]);
    }

    public function updatedSearch(): void
    {
        Log::info('RolePermissionMatrix: search updated', [
            'search_term' => $this->search
        ]);
        
        // Reset to first page when search changes
        $this->currentPage = 1;
        $this->mount();
    }

    public function updatedFilterModel(): void
    {
        Log::info('RolePermissionMatrix: filter model updated', [
            'filter_model' => $this->filterModel
        ]);
        
        // Reset to first page when filter changes
        $this->currentPage = 1;
        $this->mount();
    }

    public function updatedFilterAction(): void
    {
        Log::info('RolePermissionMatrix: filter action updated', [
            'filter_action' => $this->filterAction
        ]);
        
        // Reset to first page when filter changes
        $this->currentPage = 1;
        $this->mount();
    }

    public function clearFilters(): void
    {
        Log::info('RolePermissionMatrix: clearing all filters');
        
        $this->search = '';
        $this->filterModel = '';
        $this->filterAction = '';
        $this->currentPage = 1;
        $this->mount();
    }

    protected function extractFiltersFromPermissions(): void
    {
        // Get all permissions to extract unique models and actions
        $allPermissions = Permission::where('guard_name', 'web')->get();
        
        $models = [];
        $actions = [];
        
        foreach ($allPermissions as $permission) {
            $name = $permission->name;
            
            // Extract model name (usually after the action and a space/dash/dot)
            // Examples: "view User", "create Order", "manage-users", "access.dashboard"
            if (preg_match('/^([a-z\-]+)[\s\.\-](.+)$/i', $name, $matches)) {
                $action = $matches[1];
                $model = $matches[2];
                
                if (!in_array($action, $actions)) {
                    $actions[] = $action;
                }
                
                if (!in_array($model, $models)) {
                    $models[] = $model;
                }
            } elseif (preg_match('/^([a-z\-]+)::(.+)$/i', $name, $matches)) {
                // Handle format like "filament::access"
                $action = $matches[1];
                $model = $matches[2];
                
                if (!in_array($action, $actions)) {
                    $actions[] = $action;
                }
                
                if (!in_array($model, $models)) {
                    $models[] = $model;
                }
            } else {
                // Single word permissions (like "access app sketcher")
                $parts = explode(' ', $name);
                if (count($parts) > 0 && !in_array($parts[0], $actions)) {
                    $actions[] = $parts[0];
                }
            }
        }
        
        sort($models);
        sort($actions);
        
        $this->availableModels = $models;
        $this->availableActions = $actions;
        
        Log::info('RolePermissionMatrix: Extracted filters', [
            'models_count' => count($models),
            'actions_count' => count($actions)
        ]);
    }

    public function goToPage($page): void
    {
        Log::info('RolePermissionMatrix: goToPage() called', [
            'requested_page' => $page,
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages
        ]);

        $this->currentPage = max(1, min($page, $this->totalPages));
        $this->mount();
    }

    public function previousPage(): void
    {
        Log::info('RolePermissionMatrix: previousPage() called', [
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages
        ]);

        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->mount();
        } else {
            Log::warning('RolePermissionMatrix: previousPage() - already on first page');
        }
    }

    public function nextPage(): void
    {
        Log::info('RolePermissionMatrix: nextPage() called', [
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages
        ]);

        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
            $this->mount();
        } else {
            Log::warning('RolePermissionMatrix: nextPage() - already on last page');
        }
    }

    public function togglePermission($roleId, $permissionId): void
    {
        Log::info('RolePermissionMatrix: togglePermission() called', [
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'current_state' => $this->rolePermissions[$roleId][$permissionId] ?? 'unknown'
        ]);

        try {
            $role = Role::findOrFail($roleId);
            $permission = Permission::findOrFail($permissionId);

            Log::info('RolePermissionMatrix: Role and Permission found', [
                'role_name' => $role->name,
                'role_display_name' => $role->display_name,
                'permission_name' => $permission->name,
                'permission_display_name' => $permission->display_name
            ]);

            if ($this->rolePermissions[$roleId][$permissionId]) {
                // Revoke permission
                Log::info('RolePermissionMatrix: Revoking permission', [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId
                ]);

                $role->revokePermissionTo($permission->name);
                $this->rolePermissions[$roleId][$permissionId] = false;
                
                Notification::make()
                    ->title('Разрешение отозвано')
                    ->body("Разрешение \"{$permission->display_name}\" отозвано у роли \"{$role->display_name}\"")
                    ->success()
                    ->send();

                Log::info('RolePermissionMatrix: Permission revoked successfully');
            } else {
                // Grant permission
                Log::info('RolePermissionMatrix: Granting permission', [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId
                ]);

                $role->givePermissionTo($permission->name);
                $this->rolePermissions[$roleId][$permissionId] = true;
                
                Notification::make()
                    ->title('Разрешение предоставлено')
                    ->body("Разрешение \"{$permission->display_name}\" предоставлено роли \"{$role->display_name}\"")
                    ->success()
                    ->send();

                Log::info('RolePermissionMatrix: Permission granted successfully');
            }

            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            Log::info('RolePermissionMatrix: Permission cache cleared');

        } catch (\Exception $e) {
            Log::error('RolePermissionMatrix: togglePermission() failed', [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Ошибка')
                ->body('Не удалось обновить разрешение: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function toggleAllPermissionsForRole($roleId): void
    {
        Log::info('RolePermissionMatrix: toggleAllPermissionsForRole() called', [
            'role_id' => $roleId,
            'total_permissions' => count($this->permissions)
        ]);

        try {
            $role = Role::findOrFail($roleId);
            
            Log::info('RolePermissionMatrix: Role found for toggle all', [
                'role_name' => $role->name,
                'role_display_name' => $role->display_name
            ]);

            // Check if all permissions are currently granted
            $allGranted = true;
            foreach ($this->permissions as $permission) {
                if (!$this->rolePermissions[$roleId][$permission['id']]) {
                    $allGranted = false;
                    break;
                }
            }

            Log::info('RolePermissionMatrix: All granted status', [
                'all_granted' => $allGranted
            ]);

            if ($allGranted) {
                // Revoke all
                Log::info('RolePermissionMatrix: Revoking all permissions from role', [
                    'role_id' => $roleId
                ]);

                foreach ($this->permissions as $permission) {
                    $role->revokePermissionTo($permission['name']);
                    $this->rolePermissions[$roleId][$permission['id']] = false;
                }
                
                Notification::make()
                    ->title('Все разрешения отозваны')
                    ->body("Все разрешения отозваны у роли \"{$role->display_name}\"")
                    ->success()
                    ->send();

                Log::info('RolePermissionMatrix: All permissions revoked from role');
            } else {
                // Grant all
                Log::info('RolePermissionMatrix: Granting all permissions to role', [
                    'role_id' => $roleId
                ]);

                foreach ($this->permissions as $permission) {
                    $role->givePermissionTo($permission['name']);
                    $this->rolePermissions[$roleId][$permission['id']] = true;
                }
                
                Notification::make()
                    ->title('Все разрешения предоставлены')
                    ->body("Все разрешения предоставлены роли \"{$role->display_name}\"")
                    ->success()
                    ->send();

                Log::info('RolePermissionMatrix: All permissions granted to role');
            }

            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            Log::info('RolePermissionMatrix: Permission cache cleared after toggle all for role');

        } catch (\Exception $e) {
            Log::error('RolePermissionMatrix: toggleAllPermissionsForRole() failed', [
                'role_id' => $roleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Ошибка')
                ->body('Не удалось обновить разрешения: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function toggleAllRolesForPermission($permissionId): void
    {
        Log::info('RolePermissionMatrix: toggleAllRolesForPermission() called', [
            'permission_id' => $permissionId,
            'total_roles' => count($this->roles)
        ]);

        try {
            $permission = Permission::findOrFail($permissionId);
            
            Log::info('RolePermissionMatrix: Permission found for toggle all roles', [
                'permission_name' => $permission->name,
                'permission_display_name' => $permission->display_name
            ]);

            // Check if all roles currently have this permission
            $allGranted = true;
            foreach ($this->roles as $roleData) {
                if (!$this->rolePermissions[$roleData['id']][$permissionId]) {
                    $allGranted = false;
                    break;
                }
            }

            Log::info('RolePermissionMatrix: All roles granted status', [
                'all_granted' => $allGranted
            ]);

            if ($allGranted) {
                // Revoke from all roles
                Log::info('RolePermissionMatrix: Revoking permission from all roles', [
                    'permission_id' => $permissionId
                ]);

                foreach ($this->roles as $roleData) {
                    $role = Role::find($roleData['id']);
                    $role->revokePermissionTo($permission->name);
                    $this->rolePermissions[$roleData['id']][$permissionId] = false;
                }
                
                Notification::make()
                    ->title('Разрешение отозвано у всех ролей')
                    ->body("Разрешение \"{$permission->display_name}\" отозвано у всех ролей")
                    ->success()
                    ->send();

                Log::info('RolePermissionMatrix: Permission revoked from all roles');
            } else {
                // Grant to all roles
                Log::info('RolePermissionMatrix: Granting permission to all roles', [
                    'permission_id' => $permissionId
                ]);

                foreach ($this->roles as $roleData) {
                    $role = Role::find($roleData['id']);
                    $role->givePermissionTo($permission->name);
                    $this->rolePermissions[$roleData['id']][$permissionId] = true;
                }
                
                Notification::make()
                    ->title('Разрешение предоставлено всем ролям')
                    ->body("Разрешение \"{$permission->display_name}\" предоставлено всем ролям")
                    ->success()
                    ->send();

                Log::info('RolePermissionMatrix: Permission granted to all roles');
            }

            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            Log::info('RolePermissionMatrix: Permission cache cleared after toggle all roles');

        } catch (\Exception $e) {
            Log::error('RolePermissionMatrix: toggleAllRolesForPermission() failed', [
                'permission_id' => $permissionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Ошибка')
                ->body('Не удалось обновить разрешения: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')
                ->label('Вернуться к списку')
                ->icon('heroicon-o-arrow-left')
                ->url(RoleResource::getUrl('index')),
        ];
    }
}


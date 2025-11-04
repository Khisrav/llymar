<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

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

    public function mount(): void
    {
        // Load roles and convert to array
        $this->roles = Role::where('guard_name', 'web')
            ->orderBy('id')
            ->get()
            ->toArray();
        
        // Load all permissions and apply custom sorting
        $allPermissions = Permission::where('guard_name', 'web')->get();
        
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

        // Get paginated permissions
        $paginatedPermissions = array_slice(
            $this->permissions, 
            ($this->currentPage - 1) * $this->perPage, 
            $this->perPage
        );

        // Group paginated permissions by prefix
        $grouped = collect($paginatedPermissions)->groupBy(function ($permission) {
            $parts = explode('.', $permission['name']);
            return count($parts) > 1 ? $parts[0] : 'other';
        });

        // Convert grouped permissions to array
        $this->groupedPermissions = $grouped->map(function ($group) {
            return $group->toArray();
        })->toArray();

        // Load existing role-permission relationships for all permissions
        $rolesCollection = Role::where('guard_name', 'web')->orderBy('id')->get();
        $permissionsCollection = Permission::where('guard_name', 'web')->get();
        
        foreach ($rolesCollection as $role) {
            foreach ($permissionsCollection as $permission) {
                $this->rolePermissions[$role->id][$permission->id] = $role->hasPermissionTo($permission->name);
            }
        }
    }

    public function goToPage($page): void
    {
        $this->currentPage = max(1, min($page, $this->totalPages));
        $this->mount();
    }

    public function previousPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->mount();
        }
    }

    public function nextPage(): void
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
            $this->mount();
        }
    }

    public function togglePermission($roleId, $permissionId): void
    {
        try {
            $role = Role::findOrFail($roleId);
            $permission = Permission::findOrFail($permissionId);

            if ($this->rolePermissions[$roleId][$permissionId]) {
                // Revoke permission
                $role->revokePermissionTo($permission->name);
                $this->rolePermissions[$roleId][$permissionId] = false;
                
                Notification::make()
                    ->title('Разрешение отозвано')
                    ->body("Разрешение \"{$permission->display_name}\" отозвано у роли \"{$role->display_name}\"")
                    ->success()
                    ->send();
            } else {
                // Grant permission
                $role->givePermissionTo($permission->name);
                $this->rolePermissions[$roleId][$permissionId] = true;
                
                Notification::make()
                    ->title('Разрешение предоставлено')
                    ->body("Разрешение \"{$permission->display_name}\" предоставлено роли \"{$role->display_name}\"")
                    ->success()
                    ->send();
            }

            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка')
                ->body('Не удалось обновить разрешение: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function toggleAllPermissionsForRole($roleId): void
    {
        try {
            $role = Role::findOrFail($roleId);
            
            // Check if all permissions are currently granted
            $allGranted = true;
            foreach ($this->permissions as $permission) {
                if (!$this->rolePermissions[$roleId][$permission['id']]) {
                    $allGranted = false;
                    break;
                }
            }

            if ($allGranted) {
                // Revoke all
                foreach ($this->permissions as $permission) {
                    $role->revokePermissionTo($permission['name']);
                    $this->rolePermissions[$roleId][$permission['id']] = false;
                }
                
                Notification::make()
                    ->title('Все разрешения отозваны')
                    ->body("Все разрешения отозваны у роли \"{$role->display_name}\"")
                    ->success()
                    ->send();
            } else {
                // Grant all
                foreach ($this->permissions as $permission) {
                    $role->givePermissionTo($permission['name']);
                    $this->rolePermissions[$roleId][$permission['id']] = true;
                }
                
                Notification::make()
                    ->title('Все разрешения предоставлены')
                    ->body("Все разрешения предоставлены роли \"{$role->display_name}\"")
                    ->success()
                    ->send();
            }

            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка')
                ->body('Не удалось обновить разрешения: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function toggleAllRolesForPermission($permissionId): void
    {
        try {
            $permission = Permission::findOrFail($permissionId);
            
            // Check if all roles currently have this permission
            $allGranted = true;
            foreach ($this->roles as $roleData) {
                if (!$this->rolePermissions[$roleData['id']][$permissionId]) {
                    $allGranted = false;
                    break;
                }
            }

            if ($allGranted) {
                // Revoke from all roles
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
            } else {
                // Grant to all roles
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
            }

            // Clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        } catch (\Exception $e) {
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


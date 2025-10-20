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

    public function mount(): void
    {
        // Load roles and convert to array
        $this->roles = Role::where('guard_name', 'web')
            ->orderBy('id')
            ->get()
            ->toArray();
        
        // Load permissions and convert to array
        $this->permissions = Permission::where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->toArray();

        // Group permissions by prefix (e.g., "user.view", "user.edit" -> "user")
        $grouped = collect($this->permissions)->groupBy(function ($permission) {
            $parts = explode('.', $permission['name']);
            return count($parts) > 1 ? $parts[0] : 'other';
        });

        // Convert grouped permissions to array
        $this->groupedPermissions = $grouped->map(function ($group) {
            return $group->toArray();
        })->toArray();

        // Load existing role-permission relationships
        $rolesCollection = Role::where('guard_name', 'web')->orderBy('id')->get();
        $permissionsCollection = Permission::where('guard_name', 'web')->orderBy('name')->get();
        
        foreach ($rolesCollection as $role) {
            foreach ($permissionsCollection as $permission) {
                $this->rolePermissions[$role->id][$permission->id] = $role->hasPermissionTo($permission->name);
            }
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


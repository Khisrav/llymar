<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NewsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create news permissions
        $permissions = [
            'view_any_news' => 'Просмотр всех новостей в админке',
            'view_news' => 'Просмотр новости в админке',
            'create_news' => 'Создание новостей',
            'update_news' => 'Редактирование всех новостей',
            'update_own_news' => 'Редактирование собственных новостей',
            'delete_news' => 'Удаление всех новостей',
            'delete_own_news' => 'Удаление собственных новостей',
            'restore_news' => 'Восстановление новостей',
            'force_delete_news' => 'Полное удаление новостей',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        // Assign permissions to super-admin role
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo(array_keys($permissions));
        }

        // Assign basic permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'view_any_news',
                'view_news',
                'create_news',
                'update_news',
                'delete_news',
            ]);
        }

        // Assign limited permissions to editor role (if exists)
        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole) {
            $editorRole->givePermissionTo([
                'view_any_news',
                'view_news',
                'create_news',
                'update_own_news',
                'delete_own_news',
            ]);
        }

        $this->command->info('News permissions seeded successfully!');
    }
} 
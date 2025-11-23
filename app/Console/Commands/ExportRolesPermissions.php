<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;

class ExportRolesPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan export:roles-permissions
     */
    protected $signature = 'export:roles-permissions {--path= : Relative path to save file (default: storage/app/roles_permissions.json)}';

    /**
     * The console command description.
     */
    protected $description = 'Export all roles and their permissions to a JSON file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching roles and permissions...');

        $roles = Role::with('permissions')->get()->map(function ($role) {
            return [
                'role' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ];
        });

        $data = $roles->toArray();

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Determine file path
        $relativePath = $this->option('path') ?? 'roles_permissions.json';
        $fullPath = storage_path('app/' . $relativePath);

        // Save file
        Storage::disk('local')->put($relativePath, $json);

        $this->info("Export complete! File saved at: {$fullPath}");

        return self::SUCCESS;
    }
}

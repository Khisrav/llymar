<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncDxfPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-dxf-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync DXF permissions for child users based on their Dealer parents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting DXF permissions sync...');
        
        // Get all Dealer users
        $dealers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Dealer');
        })->get();
        
        $syncedCount = 0;
        
        foreach ($dealers as $dealer) {
            $this->line("Processing Dealer: {$dealer->name}");
            
            // Sync all children of this dealer
            $dealer->syncChildrenDxfAccess();
            
            $childrenCount = $dealer->children->count();
            $syncedCount += $childrenCount;
            
            if ($childrenCount > 0) {
                $this->line("  └── Synced {$childrenCount} child users");
            }
        }
        
        // Also ensure all child users inherit from their dealer parents
        $childUsers = User::whereNotNull('parent_id')->get();
        
        foreach ($childUsers as $child) {
            $child->ensureDxfAccessInheritance();
        }
        
        $this->info("✅ DXF permissions sync completed!");
        $this->info("📊 Processed {$dealers->count()} dealers and synced {$syncedCount} child users");
        
        return 0;
    }
} 
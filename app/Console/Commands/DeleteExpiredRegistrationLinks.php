<?php

namespace App\Console\Commands;

use App\Models\RegistrationLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteExpiredRegistrationLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registration-links:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete registration links that have been expired for more than 48 hours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting cleanup of expired registration links...');

        // Find links that expired more than 48 hours ago and are not used
        $cutoffTime = now()->subHours(48);
        
        $expiredLinks = RegistrationLink::where('is_used', false)
            ->where('expires_at', '<=', $cutoffTime)
            ->get();

        $count = $expiredLinks->count();

        if ($count === 0) {
            $this->info('No expired registration links to delete.');
            return Command::SUCCESS;
        }

        $this->info("Found {$count} expired registration link(s) to delete.");

        try {
            // Delete the expired links
            RegistrationLink::where('is_used', false)
                ->where('expires_at', '<=', $cutoffTime)
                ->delete();

            $this->info("Successfully deleted {$count} expired registration link(s).");
            
            Log::info("Deleted {$count} expired registration links", [
                'cutoff_time' => $cutoffTime->toDateTimeString(),
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to delete expired registration links: {$e->getMessage()}");
            
            Log::error('Failed to delete expired registration links', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}


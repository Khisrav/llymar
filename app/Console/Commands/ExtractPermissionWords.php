<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use Illuminate\Support\Facades\Storage;

class ExtractPermissionWords extends Command
{
    protected $signature = 'permissions:extract-words {--output=permission_words.txt}';
    protected $description = 'Extract all unique words from permission names and save to a text file';

    public function handle()
    {
        $this->info('Extracting unique words from permission names...');
        
        // Get all permissions from database
        $permissions = Permission::all();
        
        if ($permissions->isEmpty()) {
            $this->warn('No permissions found in database.');
            return 1;
        }
        
        $this->info("Found {$permissions->count()} permissions.");
        
        // Extract all words
        $allWords = [];
        
        foreach ($permissions as $permission) {
            if (str_starts_with($permission->name, 'access')) {
                $word = trim($permission->name);
                if (!empty($word)) {
                    $allWords[] = $word;
                }
                continue;
            }
            
            // Split by spaces and hyphens to get individual words
            $words = preg_split('/\s+/', $permission->name);
            
            foreach ($words as $word) {
                // Trim and filter empty strings
                $word = trim($word);
                if (!empty($word)) {
                    $allWords[] = $word;
                }
            }
        }
        
        // Get unique words and sort them
        $uniqueWords = array_unique($allWords);
        sort($uniqueWords);
        
        $this->info("Found {$this->count($allWords)} total words, {$this->count($uniqueWords)} unique words.");
        
        // Prepare output file path
        $outputFile = $this->option('output');
        $outputPath = storage_path('app/' . $outputFile);
        
        // Save to file - one word per line
        $content = implode("\n", $uniqueWords);
        file_put_contents($outputPath, $content);
        
        $this->info("Unique words saved to: {$outputPath}");
        
        // Display the words
        $this->newLine();
        $this->line('Unique words:');
        $this->table(['Word'], array_map(fn($word) => [$word], $uniqueWords));
        
        return 0;
    }
    
    private function count(array|int $items): int
    {
        return is_array($items) ? count($items) : $items;
    }
}


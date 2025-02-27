<?php

namespace Blaspsoft\Blasp\Console\Commands;

use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;

class BlaspClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blasp:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the Blasp profanity cache';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Clear all cache keys that start with blasp_
        $keys = Cache::get('blasp_cache_keys', []);
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        Cache::forget('blasp_cache_keys');
        
        $this->info('Blasp cache cleared successfully!');
    }
}
<?php

namespace Blaspsoft\Blasp\Console\Commands;

use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;
use Blaspsoft\Blasp\Config\ConfigurationLoader;

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
        ConfigurationLoader::clearCache();
        
        $this->info('Blasp cache cleared successfully!');
    }
}
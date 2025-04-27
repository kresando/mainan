<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizeApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the application without view caching';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting application optimization...');

        // Clear any existing caches first
        $this->info('Clearing caches...');
        Artisan::call('route:clear');
        $this->info('Routes cleared.');
        
        Artisan::call('config:clear');
        $this->info('Config cleared.');
        
        Artisan::call('cache:clear');
        $this->info('Application cache cleared.');

        // Cache configs
        $this->info('Caching configuration...');
        Artisan::call('config:cache');
        $this->info('Configuration cached successfully.');
        
        // Cache routes
        $this->info('Caching routes...');
        Artisan::call('route:cache');
        $this->info('Routes cached successfully.');
        
        // Avoid view caching since it causes errors
        $this->info('Skipping view caching due to compatibility issues.');
        
        // Optimize Composer's autoloader
        $this->info('Optimizing Composer autoloader...');
        exec('composer dump-autoload --optimize');
        $this->info('Composer autoloader optimized.');
        
        $this->info('Application optimization completed successfully!');
        
        return Command::SUCCESS;
    }
} 
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clean temporary uploaded files daily
        $schedule->command('livewire:configure-s3-upload-cleanup')
            ->daily();
        
        // Use our custom optimize command instead
        $schedule->command('app:optimize')
            ->weekly()
            ->sundays()
            ->at('3:00')
            ->environments(['production']);
            
        // Prune old database records to keep tables optimized
        $schedule->command('model:prune', [
            '--model' => [
                'App\Models\Post',
            ],
        ])->monthly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 
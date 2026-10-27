<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // HRM owns the canonical sectorCode; keep the local sector master in step.
        $schedule->command('weight-scale:sync-sectors-from-hrm')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->when(fn (): bool => (bool) config('hrm.sync_enabled'));
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

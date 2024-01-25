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
        // Programar el comando para que se ejecute cada cierto tiempo
        $schedule->command('app:send-pending-messages')->everyTenMinutes();
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    // La propiedad $commands no es necesaria si estás usando el método load()
    // protected $commands = [
    //     Commands\SendPendingMessages::class,
    // ];
}

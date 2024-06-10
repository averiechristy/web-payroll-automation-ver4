<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        \App\Console\Commands\UpdateHolidays::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Menjadwalkan update holidays untuk dijalankan setiap tahun pada tanggal 1 Januari
        $schedule->command('holidays:update')->yearlyOn(1, 1);
                    
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
{
    $this->load(__DIR__.'/Commands');

    require base_path('routes/console.php');
}

}

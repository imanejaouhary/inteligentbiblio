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
        // Marquer les emprunts en retard quotidiennement
        $schedule->command('library:mark-late')->daily();

        // Purger les tokens expirés
        $schedule->command('library:cleanup-tokens')->hourly();

        // Envoyer des notifications simulées
        $schedule->command('library:send-notifications')->hourly();

        // Nettoyage du stockage des cours (fichiers orphelins) chaque nuit
        $schedule->command('library:cleanup-storage')->dailyAt('03:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}



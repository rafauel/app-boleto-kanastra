<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define a agenda de comandos.
     */
    protected function schedule(Schedule $schedule)
    {
        // Agenda o job de reprocessamento de falhas para rodar a cada cinco minutos. Apenas para este
        $schedule->job(new \App\Jobs\ReprocessFailedJobs)->everyFiveMinutes();
    }

    /**
     * Registra os comandos customizados do aplicativo.
     */
    protected function commands()
    {
        // Carrega os comandos customizados a partir do diretório Commands.
        $this->load(__DIR__.'/Commands');

        // Requer o arquivo de rotas do console.
        require base_path('routes/console.php');
    }
}
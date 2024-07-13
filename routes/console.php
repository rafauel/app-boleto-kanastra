<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\ReprocessFailedJobs;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('reprocess:failed-jobs', function () {
    ReprocessFailedJobs::dispatch();
})->describe('Reprocessa os boletos e emails que falharam')->everyFiveMinutes();

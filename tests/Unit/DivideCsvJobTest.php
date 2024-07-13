<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\DivideCsvJob;
use App\Jobs\ProcessCsvChunkJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;

class DivideCsvJobTest extends TestCase
{
    use RefreshDatabase;

    public function testDivideCsvJob()
    {
        // Dados de exemplo
        $filePath = 'uploads/test.csv';
        $header = ['name', 'governmentId', 'email', 'debtAmount', 'debtDueDate', 'debtId'];

        // Cria um arquivo CSV de exemplo
        Storage::fake('local');
        Storage::disk('local')->put($filePath, "name,governmentId,email,debtAmount,debtDueDate,debtId\nJohn Doe,123456789,john@example.com,100.50,2024-07-10,1\nJane Doe,987654321,jane@example.com,200.75,2024-07-15,2");

        // Despacha o job
        $job = new DivideCsvJob($filePath, $header);
        Queue::fake();
        $job->handle();

        // Verifica se o job ProcessCsvChunkJob foi despachado
        Queue::assertPushed(ProcessCsvChunkJob::class);
    }
}

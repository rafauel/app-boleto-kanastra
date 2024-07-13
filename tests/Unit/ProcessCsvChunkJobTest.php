<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\ProcessCsvChunkJob;
use App\Models\Boleto;
use App\Models\ProcessingLog;
use Illuminate\Support\Facades\DB;

class ProcessCsvChunkJobTest extends TestCase
{
    use RefreshDatabase;

    public function testProcessCsvChunkJob()
    {
        // Dados de exemplo
        $chunk = [
            ['name' => 'John Doe', 'governmentId' => '123456789', 'email' => 'john@example.com', 'debtAmount' => '100.50', 'debtDueDate' => '2024-07-10', 'debtId' => '1'],
            ['name' => 'Jane Doe', 'governmentId' => '987654321', 'email' => 'jane@example.com', 'debtAmount' => '200.75', 'debtDueDate' => '2024-07-15', 'debtId' => '2'],
        ];
        $header = ['name', 'governmentId', 'email', 'debtAmount', 'debtDueDate', 'debtId'];
        $chunkIndex = 0;

        // Despacha o job
        $job = new ProcessCsvChunkJob($chunk, $header, $chunkIndex);
        $job->handle();

        // Verifica se os dados foram inseridos no banco de dados
        $this->assertDatabaseHas('boletos', ['debtId' => '1']);
        $this->assertDatabaseHas('boletos', ['debtId' => '2']);
    }
}

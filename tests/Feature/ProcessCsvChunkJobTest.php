<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use App\Jobs\ProcessCsvChunkJob;
use App\Models\Boleto;
use App\Models\ProcessingLog;
use App\Services\BoletoService;
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;

class ProcessCsvChunkJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_process_csv_chunk_and_generate_boletos()
    {
        Log::spy();

        $chunk = [
            ['John Doe', '11111111111', 'johndoe@kanastra.com.br', '1000000.00', '2022-10-12', '1adb6ccf-ff16-467f-bea7-5f05d494280f']
        ];
        $header = ['name', 'governmentId', 'email', 'debtAmount', 'debtDueDate', 'debtId'];

        $job = new ProcessCsvChunkJob($chunk, $header, 0);
        $job->handle();

        $this->assertDatabaseHas('boletos', [
            'email' => 'johndoe@kanastra.com.br',
            'boleto_generated' => true,
            'email_sent' => true,
        ]);

        Log::shouldHaveReceived('info')->with('Boleto gerado para: johndoe@kanastra.com.br');
        Log::shouldHaveReceived('info')->with('Email enviado para: johndoe@kanastra.com.br');
    }

    /** @test */
    public function it_logs_invalid_debt_amount()
    {
        $chunk = [
            ['John Doe', '11111111111', 'johndoe@kanastra.com.br', 'invalid_amount', '2022-10-12', '1adb6ccf-ff16-467f-bea7-5f05d494280f']
        ];
        $header = ['name', 'governmentId', 'email', 'debtAmount', 'debtDueDate', 'debtId'];

        $job = new ProcessCsvChunkJob($chunk, $header, 0);
        $job->handle();

        $this->assertDatabaseHas('processing_logs', [
            'debtId' => '1adb6ccf-ff16-467f-bea7-5f05d494280f',
            'message' => 'Valor inválido para debtAmount: invalid_amount',
        ]);
    }
}

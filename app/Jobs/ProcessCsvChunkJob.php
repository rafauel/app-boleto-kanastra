<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Boleto;
use App\Models\ProcessingLog;
use App\Services\BoletoService;
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;
use DB;
use Exception;

class ProcessCsvChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $timeout = 120;

    protected $chunk;
    protected $header;
    protected $chunkIndex;

    public function __construct(array $chunk, array $header, int $chunkIndex)
    {
        $this->chunk = $chunk;
        $this->header = $header;
        $this->chunkIndex = $chunkIndex;
    }

    public function handle()
    {
        $boletosData = [];
        foreach ($this->chunk as $row) {
            try {
                $rowData = array_combine($this->header, $row);

                // Valida se debtAmount é numérico, pode conter outras validações tbm
                if (!is_numeric($rowData['debtAmount'])) {
                    ProcessingLog::create([
                        'debtId' => $rowData['debtId'] ?? null,
                        'message' => 'Valor inválido para debtAmount: ' . $rowData['debtAmount']
                    ]);
                    continue;
                }

                $boletosData[] = [
                    'name' => $rowData['name'],
                    'governmentId' => $rowData['governmentId'],
                    'email' => $rowData['email'],
                    'debtAmount' => $rowData['debtAmount'],
                    'debtDueDate' => $rowData['debtDueDate'],
                    'debtId' => $rowData['debtId'],
                    'boleto_generated' => false,
                    'email_sent' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } catch (Exception $e) {
                ProcessingLog::create([
                    'debtId' => $rowData['debtId'] ?? null,
                    'message' => 'Erro ao processar linha do CSV: ' . $e->getMessage()
                ]);
            }
        }

        // Batch insert
        if (!empty($boletosData)) {
            DB::table('boletos')->upsert($boletosData, ['debtId'], ['name', 'governmentId', 'email', 'debtAmount', 'debtDueDate', 'updated_at']);
        }

        // Processar boletos e enviar e-mails
        $boletos = Boleto::whereIn('debtId', array_column($boletosData, 'debtId'))->get();
        foreach ($boletos as $boleto) {
            try {
                if (!$boleto->boleto_generated) {
                    BoletoService::generateBoleto($boleto->toArray());
                    $boleto->update(['boleto_generated' => true]);
                }

                if (!$boleto->email_sent) {
                    EmailService::sendEmail($boleto->email, $boleto->toArray());
                    $boleto->update(['email_sent' => true]);
                }
            } catch (Exception $e) {
                ProcessingLog::create([
                    'debtId' => $boleto->debtId,
                    'message' => 'Erro ao processar boleto: ' . $e->getMessage()
                ]);
                // Não falha o job aqui, apenas registra o erro
            }
        }
    }

    public function failed(Exception $exception)
    {
        // Aqui você pode enviar uma notificação, logar ou tomar qualquer outra ação necessária quando o job falhar.
        Log::error("Job falhou no chunk $this->chunkIndex: " . $exception->getMessage());
    }
}
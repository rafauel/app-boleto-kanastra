<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Boleto;
use App\Services\BoletoService;
use App\Services\EmailService;
use Exception;

class ReprocessFailedJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Recupera boletos que ainda não foram gerados ou enviados
        $boletos = Boleto::where('boleto_generated', false)
                         ->orWhere('email_sent', false)
                         ->get();

        foreach ($boletos as $boleto) {
            try {
                $rowData = $boleto->toArray();

                // Gera boleto se ainda não foi gerado
                if (!$boleto->boleto_generated) {
                    BoletoService::generateBoleto($rowData);
                    $boleto->update(['boleto_generated' => true]);
                }

                // Envia email se ainda não foi enviado
                if (!$boleto->email_sent) {
                    EmailService::sendEmail($rowData['email'], $rowData);
                    $boleto->update(['email_sent' => true]);
                }
            } catch (Exception $e) {
                // Logue o erro, se necessário
            }
        }
    }
}

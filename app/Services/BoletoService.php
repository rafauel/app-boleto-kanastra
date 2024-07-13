<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BoletoService
{
    public static function generateBoleto($data)
    {
        // Simular geração de boletos
        Log::info("Boleto gerado para: " . $data['email']);
    }
}

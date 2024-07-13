<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EmailService
{
    public static function sendEmail($email, $data)
    {
        // Simular envio de e-mails
        Log::info("Email enviado para: " . $email);
    }
}

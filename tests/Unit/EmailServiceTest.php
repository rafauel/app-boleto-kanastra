<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;

class EmailServiceTest extends TestCase
{
    /** @test */
    public function it_can_send_email()
    {
        Log::shouldReceive('info')->once()->with("Email enviado para: test@example.com");

        $data = [
            'email' => 'test@example.com'
        ];

        EmailService::sendEmail('test@example.com', $data);
    }
}

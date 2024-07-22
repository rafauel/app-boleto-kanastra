<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BoletoService;
use Illuminate\Support\Facades\Log;

class BoletoServiceTest extends TestCase
{
    /** @test */
    public function it_can_generate_boleto()
    {
        Log::shouldReceive('info')->once()->with("Boleto gerado para: test@example.com");

        $data = [
            'email' => 'test@example.com'
        ];

        BoletoService::generateBoleto($data);
    }
}

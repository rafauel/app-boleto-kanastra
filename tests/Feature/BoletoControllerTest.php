<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use App\Models\ReceivedFile;
use App\Jobs\DivideCsvJob;

class BoletoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testUploadCsv()
    {
        Storage::fake('local');
        Queue::fake();

        $file = UploadedFile::fake()->createWithContent('test.csv', "name,governmentId,email,debtAmount,debtDueDate,debtId\nJohn Doe,123456789,john@example.com,100.50,2024-07-10,1\nJane Doe,987654321,jane@example.com,200.75,2024-07-15,2");

        $response = $this->post('/upload', ['files' => [$file]]);

        $response->assertStatus(200);

        Queue::assertPushed(DivideCsvJob::class);
    }

    public function testUploadDuplicateCsv()
    {
        Storage::fake('local');
        Queue::fake();

        $file = UploadedFile::fake()->createWithContent('test.csv', "name,governmentId,email,debtAmount,debtDueDate,debtId\nJohn Doe,123456789,john@example.com,100.50,2024-07-10,1\nJane Doe,987654321,jane@example.com,200.75,2024-07-15,2");

        // Primeiro upload
        $this->post('/upload', ['files' => [$file]]);

        // Segundo upload (duplicado)
        $response = $this->post('/upload', ['files' => [$file]]);

        $response->assertStatus(400);
        $this->assertEquals('Arquivo já recebido: test.csv', $response->json('message'));
    }
}

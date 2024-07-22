<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use App\Jobs\SplitCsvJob;

class BoletoControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_upload_and_process_csv_file()
    {
        Queue::fake();

        $csvContent = "name,governmentId,email,debtAmount,debtDueDate,debtId\n";
        $csvContent .= "John Doe,11111111111,johndoe@example.com,100.00,2022-10-12,1adb6ccf-ff16-467f-bea7-5f05d494280f\n";

        $file = UploadedFile::fake()->createWithContent('input.csv', $csvContent);

        $response = $this->postJson('/api/upload', [
            'files' => [$file],
        ]);

        $response->assertStatus(200);
        Queue::assertPushed(SplitCsvJob::class);
    }

    /** @test */
    public function it_rejects_duplicate_file_upload()
    {
        $csvContent = "name,governmentId,email,debtAmount,debtDueDate,debtId\n";
        $csvContent .= "John Doe,11111111111,johndoe@example.com,100.00,2022-10-12,1adb6ccf-ff16-467f-bea7-5f05d494280f\n";

        $file = UploadedFile::fake()->createWithContent('input.csv', $csvContent);

        // Upload the file for the first time
        $this->postJson('/api/upload', [
            'files' => [$file],
        ]);

        // Try to upload the same file again
        $response = $this->postJson('/api/upload', [
            'files' => [$file],
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function it_rejects_invalid_csv_columns()
    {
        $file = UploadedFile::fake()->createWithContent('invalid.csv', 'invalid_column1,invalid_column2\nvalue1,value2');

        $response = $this->postJson('/api/upload', [
            'files' => [$file],
        ]);

        $response->assertStatus(400);
    }
}
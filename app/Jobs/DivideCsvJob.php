<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\ProcessCsvChunkJob;
use Illuminate\Support\Facades\Log;
use Exception;

class DivideCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    protected $filePath;
    protected $header;

    public function __construct($filePath, $header)
    {
        $this->filePath = $filePath;
        $this->header = $header;
    }

    public function handle()
    {
        $absoluteFilePath = storage_path('app/' . $this->filePath);

        if (!file_exists($absoluteFilePath)) {
            Log::error('Arquivo não encontrado: ' . $absoluteFilePath);
            return;
        }

        $data = array_map('str_getcsv', file($absoluteFilePath));
        $chunkSize = 1000;
        $chunks = array_chunk($data, $chunkSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            ProcessCsvChunkJob::dispatch($chunk, $this->header, $chunkIndex);
        }
    }

    public function failed(Exception $exception)
    {
        Log::error('Job DivideCsvJob falhou: ' . $exception->getMessage());
    }
}

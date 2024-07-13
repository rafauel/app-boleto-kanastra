<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\DivideCsvJob;
use App\Models\ReceivedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BoletoController extends Controller
{
    protected $expectedColumns = ['name', 'governmentId', 'email', 'debtAmount', 'debtDueDate', 'debtId'];

    public function upload(Request $request)
    {
        // Verifica se o método da requisição é GET
        if ($request->isMethod('get')) {
            return response()->json(['message' => 'GET request received'], 200);
        }

        // Valida os arquivos recebidos
        $request->validate([
            'files.*' => 'required|file|mimes:csv,txt|max:10737418240',
        ]);

        // Processa cada arquivo enviado
        $uploadedFiles = $request->file('files');
        foreach ($uploadedFiles as $file) {
            // Armazena o arquivo no diretório 
            $filePath = $file->store('uploads');

            // Calcula o hash do arquivo para que evite duplicados
            $absoluteFilePath = storage_path('app/' . $filePath);
            $fileHash = md5_file($absoluteFilePath);

            // Verifica na base se o arquivo já foi recebido anteriormente
            if (ReceivedFile::where('file_hash', $fileHash)->exists()) {
                Log::info('Arquivo já recebido: ' . $file->getClientOriginalName());
                // Deleta o arquivo pois não será utilizado
                Storage::delete($filePath); 
                return response()->json(['message' => 'Arquivo já recebido: ' . $file->getClientOriginalName()], 400);
            }

            // Verifica as colunas do CSV, garantindo que não havera erro quando for processar. 
            $data = array_map('str_getcsv', file($absoluteFilePath));
            $header = array_shift($data);

            if ($this->validateColumns($header)) {
                // Armazena informações do arquivo recebido na base
                ReceivedFile::create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_hash' => $fileHash,
                ]);

                // Despacha o job para dividir e processar o arquivo CSV em partes
                DivideCsvJob::dispatch($filePath, $header);
            } else {
                Log::error('CSV com colunas inválidas: ' . implode(', ', $header));
                Storage::delete($filePath); // Deleta o arquivo com colunas inválidas
                return response()->json(['message' => 'CSV com colunas inválidas: ' . implode(', ', $header)], 400);
            }
        }

        return response()->json(['message' => 'Arquivos enviados para processamento'], 200);
    }

    protected function validateColumns($header)
    {
        // Valida se as colunas esperadas estão presentes no arquivo CSV
        return empty(array_diff($this->expectedColumns, $header));
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SplitCsvJob;
use App\Models\ReceivedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BoletoController extends Controller
{
    protected $expectedColumns = ['name', 'governmentId', 'email', 'debtAmount', 'debtDueDate', 'debtId'];

    public function upload(Request $request)
    {
        // Valida os arquivos recebidos
        $request->validate([
            'files.*' => 'required|file|mimes:csv,txt|max:10737418240',
        ]);

        // Processa cada arquivo enviado
        $uploadedFiles = $request->file('files');
        foreach ($uploadedFiles as $file) {
            // Armazena o arquivo no diretório 
            $filePath = $file->store('uploads');
            // Calcula o hash do arquivo para evitar duplicidade
            $absoluteFilePath = storage_path('app/' . $filePath);
            $fileHash = md5_file($absoluteFilePath);
            // Verifica na base se o arquivo já foi recebido anteriormente
            if (ReceivedFile::where('file_hash', $fileHash)->exists()) {
                Log::info('Arquivo já recebido: ' . $file->getClientOriginalName());
                // Deleta o arquivo pois não será utilizado
                Storage::delete($filePath);
                return response()->json(['message' => 'Arquivo já recebido: ' . $file->getClientOriginalName()], 400);
            }

            // Verifica as colunas do CSV, garantindo que não haverá erro quando for processar. 
            $data = array_map('str_getcsv', file($absoluteFilePath));
            $header = array_shift($data);

            if (!$this->validateColumns($header)) {
                Log::error('CSV com colunas inválidas: ' . (is_array($header) ? implode(', ', $header) : 'nenhuma coluna válida encontrada'));
                Storage::delete($filePath); // Deleta o arquivo com colunas inválidas
                return response()->json(['message' => 'CSV com colunas inválidas: ' . (is_array($header) ? implode(', ', $header) : 'nenhuma coluna válida encontrada')], 400);
            }

            // Armazena informações do arquivo recebido na base
            ReceivedFile::create([
                'file_name' => $file->getClientOriginalName(),
                'file_hash' => $fileHash,
            ]);

            // Despacha o job para dividir e processar o arquivo CSV em partes
            SplitCsvJob::dispatch($filePath, $header);
        }

        return response()->json(['message' => 'Arquivos enviados para processamento'], 200);
    }

    protected function validateColumns($header)
    {
        // Valida se as colunas esperadas estão presentes no arquivo CSV
        if (!is_array($header)) {
            return false;
        }
        return empty(array_diff($this->expectedColumns, $header));
    }
}

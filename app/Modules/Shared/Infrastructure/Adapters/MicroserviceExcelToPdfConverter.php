<?php

namespace App\Modules\Shared\Infrastructure\Adapters;

use App\Modules\Shared\Domain\Contracts\ExcelToPdfConverterInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class MicroserviceExcelToPdfConverter implements ExcelToPdfConverterInterface
{
    public function convert(string $excelFilePath): string
    {
        $url = config('services.convertidor.url') . '/api/convertir/excel-a-pdf';
        $apiKey = config('services.convertidor.api_key');

        if (!file_exists($excelFilePath)) {
            throw new Exception("El archivo Excel temporal no existe.");
        }

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
        ])->attach(
            'documento', file_get_contents($excelFilePath), basename($excelFilePath)
        )->post($url);

        if ($response->successful()) {
            return $response->body();
        }

        $error = 'Error desconocido';
        if ($response->json('error')) {
            $error = $response->json('error');
        } elseif ($response->body()) {
            $error = $response->body();
        }

        throw new Exception("Error al convertir Excel a PDF en microservicio: {$error}");
    }
}

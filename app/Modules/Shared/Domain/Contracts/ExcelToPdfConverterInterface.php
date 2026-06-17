<?php

namespace App\Modules\Shared\Domain\Contracts;

interface ExcelToPdfConverterInterface
{
    /**
     * Convierte un archivo Excel a PDF.
     *
     * @param string $excelFilePath Ruta del archivo Excel a convertir.
     * @return string Contenido binario del PDF.
     * @throws \Exception Si ocurre un error en la conversión.
     */
    public function convert(string $excelFilePath): string;
}

<?php

namespace App\Exports;

use App\Models\CpPedido;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Ilovepdf\Ilovepdf;
use Exception;

/**
 * Generates an Excel file, saves it temporarily, 
 * converts it to PDF using ILovePDF API, and returns the PDF stream.
 */
class CpPedidoPdfExport extends CpPedidoExport
{
    /**
     * Generate and stream the PDF file for a pedido.
     */
    public function generate(int $pedidoId): StreamedResponse
    {
        $pedido = CpPedido::with([
            'solicitante',
            'tipoSolicitud',
            'sede',
            'elaboradoPor.rol',
            'procesoCompra.rol',
            'responsableAprobacion.rol',
            'items.producto',
        ])->findOrFail($pedidoId);

        $templatePath = storage_path('app/templates/plantilla_pedidos.xlsx');

        if (!file_exists($templatePath)) {
            throw new Exception('No se encontró la plantilla de pedidos.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $this->sheet = $spreadsheet->getActiveSheet();

        $items = $pedido->items;

        // Dynamic row adjustment for > 12 items (inherited)
        $this->extra = max(0, $items->count() - 12);
        if ($this->extra > 0) {
            $this->insertarFilasExtra();
        }

        // Fill data
        $this->llenarEncabezado($pedido);
        $this->llenarItems($items);
        $this->insertarFirmas($pedido);
        $this->responsableProceso($pedido);

        // Build filename
        $proceso     = $this->sanitize($pedido->solicitante?->nombre ?? 'SIN_PROCESO');
        $sede        = $this->sanitize($pedido->sede?->nombre ?? 'SIN_SEDE');
        $consecutivo = $this->sanitize($pedido->consecutivo ?? 'SIN_CONSECUTIVO');
        $filename    = "N.{$consecutivo} SOLICITUD DE PEDIDO {$proceso} {$sede}.pdf";

        // Save Excel temporarily
        $tempExcelPath = tempnam(sys_get_temp_dir(), 'pedido_excel_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempExcelPath);
        $spreadsheet->disconnectWorksheets();

        // Convert to PDF using ILovePDF
        $publicKey = env('ILOVEPDF_PUBLIC_KEY');
        $secretKey = env('ILOVEPDF_SECRET_KEY') ?: env('API_KEY_ILOVEPDF');

        if (!$publicKey || !$secretKey) {
            @unlink($tempExcelPath);
            throw new Exception('Faltan las credenciales de ILovePDF en el archivo .env (ILOVEPDF_PUBLIC_KEY y ILOVEPDF_SECRET_KEY).');
        }

        try {
            $ilovepdf = new Ilovepdf($publicKey, $secretKey);
            $task = $ilovepdf->newTask('officepdf');
            $file = $task->addFile($tempExcelPath);
            $task->execute();
            
            $downloadPath = sys_get_temp_dir();
            $task->download($downloadPath);
            
            // The file is downloaded as task_output_filename inside $downloadPath
            $pdfFileName = $task->outputFileName;
            if (!$pdfFileName) {
                // Si no se puede obtener el nombre, suele guardarse con un nombre generado
                // Buscamos el último archivo creado en el dir temporal
                throw new Exception('No se pudo determinar el archivo descargado de ILovePDF.');
            }
            
            $downloadedPdfPath = $downloadPath . DIRECTORY_SEPARATOR . $pdfFileName;
            
            if (!file_exists($downloadedPdfPath)) {
                throw new Exception('El archivo PDF no se descargó correctamente.');
            }
            
            $pdfContent = file_get_contents($downloadedPdfPath);
            @unlink($downloadedPdfPath);
            @unlink($tempExcelPath);

            return new StreamedResponse(function () use ($pdfContent) {
                echo $pdfContent;
            }, 200, [
                'Content-Type'                => 'application/pdf',
                'Content-Disposition'         => 'attachment; filename="' . $filename . '"',
                'Content-Length'              => strlen($pdfContent),
                'Cache-Control'               => 'max-age=0',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
            ]);
            
        } catch (\Exception $e) {
            @unlink($tempExcelPath);
            throw new Exception('Error en conversión con ILovePDF: ' . $e->getMessage());
        }
    }

}

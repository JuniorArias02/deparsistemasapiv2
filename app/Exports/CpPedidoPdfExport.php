<?php

namespace App\Exports;

use App\Models\CpPedido;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf as MpdfWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

/**
 * Generates a PDF that is identical to the Excel export,
 * by reusing ALL the template-filling logic from CpPedidoExport
 * and switching only the final writer to PhpSpreadsheet's Mpdf renderer.
 */
class CpPedidoPdfExport extends CpPedidoExport
{
    /**
     * Generate and stream the PDF file for a pedido.
     * Same data, same layout as the Excel — just rendered as PDF.
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

        // Fill data — exact same methods as Excel export (inherited)
        $this->llenarEncabezado($pedido);
        $this->llenarItems($items);
        $this->insertarFirmas($pedido);
        $this->responsableProceso($pedido);

        // Build filename — same convention but .pdf
        $proceso     = $this->sanitize($pedido->solicitante?->nombre ?? 'SIN_PROCESO');
        $sede        = $this->sanitize($pedido->sede?->nombre ?? 'SIN_SEDE');
        $consecutivo = $this->sanitize($pedido->consecutivo ?? 'SIN_CONSECUTIVO');
        $filename    = "N.{$consecutivo} SOLICITUD DE PEDIDO {$proceso} {$sede}.pdf";

        // Register the mPDF renderer for PhpSpreadsheet
        IOFactory::registerWriter('Pdf', MpdfWriter::class);

        // Capture PDF output to a string buffer
        $tempFile = tempnam(sys_get_temp_dir(), 'pedido_pdf_') . '.pdf';

        $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
        $writer->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        $pdfContent = file_get_contents($tempFile);
        @unlink($tempFile);

        return new StreamedResponse(function () use ($pdfContent) {
            echo $pdfContent;
        }, 200, [
            'Content-Type'                => 'application/pdf',
            'Content-Disposition'         => 'attachment; filename="' . $filename . '"',
            'Content-Length'              => strlen($pdfContent),
            'Cache-Control'               => 'max-age=0',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
        ]);
    }
}

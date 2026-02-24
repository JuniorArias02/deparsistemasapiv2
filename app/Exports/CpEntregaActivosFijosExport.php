<?php

namespace App\Exports;

use App\Models\CpEntregaActivosFijos;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CpEntregaActivosFijosExport
{
    public function generate(int $id): StreamedResponse
    {
        $entrega = CpEntregaActivosFijos::with([
            'personal.cargo',
            'sede',
            'procesoSolicitante',
            'coordinador',
            'items.inventario'
        ])->findOrFail($id);

        $templatePath = storage_path('app/templates/plantilla_entrega_activos_fijos.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Header Data
        if ($entrega->fecha_entrega) {
            $fecha = $entrega->fecha_entrega;
            $sheet->setCellValue('B8', $fecha->format('d'));
            $sheet->setCellValue('C8', $fecha->format('m'));
            $sheet->setCellValue('D8', $fecha->format('Y'));
        }

        $sheet->setCellValue('O6', $entrega->coordinador->nombre ?? 'N/A');
        $sheet->setCellValue('O8', $entrega->sede->nombre ?? 'N/A');
        $sheet->setCellValue('H6', $entrega->personal->nombre ?? 'N/A');
        $sheet->setCellValue('H7', $entrega->personal->cedula ?? 'N/A');
        $sheet->setCellValue('H8', $entrega->personal->cargo->nombre ?? 'N/A');
        $sheet->setCellValue('O7', $entrega->procesoSolicitante->nombre ?? 'N/A');

        // Signatures
        $this->insertFirma($sheet, $entrega->firma_quien_entrega, 'H20', 5, 10);
        $this->insertFirma($sheet, $entrega->firma_quien_recibe, 'S20', -10, 10);

        // Dynamic Items
        $startRow = 14;
        $templateRows = 5;
        $items = $entrega->items;
        $totalItems = $items->count();

        if ($totalItems > $templateRows) {
            $extraRows = $totalItems - $templateRows;
            $sheet->insertNewRowBefore($startRow + $templateRows, $extraRows);

            // Re-apply merges to new rows
            for ($r = $startRow + $templateRows; $r < $startRow + $totalItems; $r++) {
                $sheet->mergeCells("B{$r}:D{$r}");
                $sheet->mergeCells("E{$r}:F{$r}");
            }
        }

        foreach ($items as $i => $item) {
            $row = $startRow + $i;
            $inv = $item->inventario;

            $sheet->getRowDimension($row)->setVisible(true);

            $sheet->setCellValue("B{$row}", $inv->nombre ?? 'N/A');
            $sheet->setCellValue("E{$row}", $inv->proveedor ?? 'N/A');
            $sheet->setCellValue("G{$row}", $inv->num_factu ?? 'N/A');
            $sheet->setCellValue("H{$row}", $inv->marca ?? 'N/A');
            $sheet->setCellValue("I{$row}", $inv->modelo ?? 'N/A');
            $sheet->setCellValue("J{$row}", $inv->serial ?? 'N/A');
            $sheet->setCellValue("K{$row}", $inv->codigo ?? 'N/A');

            // Prefix Logic
            if ($inv && $inv->codigo) {
                $prefix = preg_replace('/[^A-Z]/i', '', $inv->codigo);
                $map = ["EB" => "L", "MAQ" => "M", "ME" => "N", "EC" => "O", "MC" => "P"];
                if (isset($map[$prefix])) {
                    $sheet->setCellValue($map[$prefix] . $row, "X");
                }
            }

            $sheet->setCellValue($item->es_accesorio ? "R{$row}" : "S{$row}", "X");
            $sheet->setCellValue("Q{$row}", $inv->estado ?? 'N/A');
            $sheet->setCellValue("T{$row}", $item->accesorio_descripcion ?? 'N/A');
            $sheet->setCellValue("U{$row}", $inv->observaciones ?? 'N/A');

            $sheet->getStyle("B{$row}:U{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            // Quitar negrillas y ajustar texto en observaciones (columna U)
            $sheet->getStyle("B{$row}:U{$row}")->getFont()->setBold(false);
            $sheet->getStyle("U{$row}")->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($row)->setRowHeight(-1); // Altura automática
        }

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        });

        $filename = "entrega_activos_" . $entrega->id . ".xlsx";
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    private function insertFirma($sheet, $path, $cell, $offsetX = 250, $offsetY = 15)
    {
        if (empty($path)) return;

        // Ensure path is relative to public or absolute
        $fullPath = public_path($path);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/public/' . $path);
            if (!file_exists($fullPath)) return;
        }

        preg_match('/([A-Z]+)([0-9]+)/', $cell, $m);
        $row = (int)$m[2];

        $sheet->getRowDimension($row)->setRowHeight(56);

        $drawing = new Drawing();
        $drawing->setPath($fullPath);
        $drawing->setCoordinates($cell);
        $drawing->setResizeProportional(false);
        $drawing->setWidth(210);
        $drawing->setHeight(60);
        $drawing->setOffsetX($offsetX);
        $drawing->setOffsetY($offsetY);
        $drawing->setWorksheet($sheet);
    }
}

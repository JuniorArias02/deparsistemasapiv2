<?php

namespace App\Exports;

use App\Models\Mantenimiento;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MantenimientoExport
{
    public function generate($maintenances, $technician): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mantenimientos');

        // Professional Header Section
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', 'DEPARTAMENTO DE SISTEMAS - REPORTE DE MANTENIMIENTOS');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:M2');
        $sheet->setCellValue('A2', 'Generado el: ' . date('d/m/Y H:i A'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data Headers (Row 4)
        $startRow = 4;
        $headers = [
            'ID',
            'Fecha Creación',
            'Título',
            'Código / Placa',
            'Modelo',
            'Dependencia',
            'Sede',
            'Coordinador',
            'Técnico(s) Asignado(s)',
            'Descripción Detallada',
            'Evidencia Fotográfica',
            'Estado / Revisión',
            'Fecha Revisado'
        ];

        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . $startRow, $header);
            $sheet->getColumnDimension($columnIndex)->setAutoSize(true);
            $columnIndex++;
        }

        // Apply style to headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'] // Tailwind slate-800
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']]
            ]
        ];
        $sheet->getStyle('A' . $startRow . ':' . chr(ord('A') + count($headers) - 1) . $startRow)->applyFromArray($headerStyle);
        $sheet->getRowDimension($startRow)->setRowHeight(30);

        // Data
        $row = $startRow + 1;
        foreach ($maintenances as $m) {
            $sheet->setCellValue('A' . $row, $m->id);
            $sheet->setCellValue('B' . $row, $m->fecha_creacion ? $m->fecha_creacion->format('d/m/Y') : '—');
            $sheet->setCellValue('C' . $row, $m->titulo);
            $sheet->setCellValue('D' . $row, $m->codigo ?? '—');
            $sheet->setCellValue('E' . $row, $m->modelo ?? '—');
            $sheet->setCellValue('F' . $row, $m->dependencia ?? '—');
            $sheet->setCellValue('G' . $row, $m->sede->nombre ?? '—');
            $sheet->setCellValue('H' . $row, $m->coordinador->nombre_completo ?? '—');

            // Extract Technicians from Agendas
            $tecnicos = $m->agendas->pluck('tecnico.nombre_completo')->unique()->filter()->implode(', ');
            $sheet->setCellValue('I' . $row, !empty($tecnicos) ? $tecnicos : '—');

            $sheet->setCellValue('J' . $row, $m->descripcion ?? '—');

            // Handle Images (Column K)
            if (!empty($m->imagen)) {
                $imagePaths = explode(',', $m->imagen);
                $offsetX = 5;
                foreach ($imagePaths as $path) {
                    $fullPath = public_path($path);
                    if (file_exists($fullPath)) {
                        $drawing = new Drawing();
                        $drawing->setPath($fullPath);
                        $drawing->setCoordinates('K' . $row);
                        $drawing->setHeight(75);
                        $drawing->setOffsetX($offsetX);
                        $drawing->setOffsetY(5);
                        $drawing->setWorksheet($sheet);
                        $offsetX += 100; // Space for next image
                    }
                }
                $sheet->getRowDimension($row)->setRowHeight(85);
                $sheet->getColumnDimension('K')->setWidth(40);
                $sheet->setCellValue('K' . $row, ""); // Clear placeholder
            } else {
                $sheet->setCellValue('K' . $row, 'Sin imágenes');
                $sheet->getRowDimension($row)->setRowHeight(30);
            }

            // Review status
            $revisadoPor = $m->revisador->nombre_completo ?? 'No revisado';
            $sheet->setCellValue('L' . $row, $m->esta_revisado ? "Revisado por: $revisadoPor" : "Pendiente");
            $sheet->setCellValue('M' . $row, $m->fecha_revisado ? $m->fecha_revisado->format('d/m/Y H:i') : '—');

            // Cell styling
            $sheet->getStyle('A' . $row . ':M' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('J' . $row)->getAlignment()->setWrapText(true);
            $sheet->getColumnDimension('J')->setWidth(50);

            // Zebra shading
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':M' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
            }

            $row++;
        }

        // Apply borders to all data
        $lastDataRow = $row - 1;
        $sheet->getStyle('A' . $startRow . ':M' . $lastDataRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        // Personal Footer Section
        $row += 2;
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", "________________________________");
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", "Firma del Técnico");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", $technician->nombre_completo ?? 'N/A');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        });

        $filename = "informe_mantenimientos_" . date('Ymd_His') . ".xlsx";
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}

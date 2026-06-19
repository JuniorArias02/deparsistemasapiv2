<?php

namespace App\Modules\GestionSistemas\Application\UseCases\EquiposComputo;

use App\Models\PcEquipo;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Storage;
use Exception;

class ExportarHojaVidaEquipoExcelUseCase
{
    public function execute(int $id): string
    {
        $equipo = PcEquipo::with([
            'sede', 
            'area', 
            'responsable', 
            'caracteristicasTecnicas.monitorInventario', 
            'caracteristicasTecnicas.tecladoInventario', 
            'caracteristicasTecnicas.mouseInventario'
        ])->find($id);

        if (!$equipo) {
            throw new Exception('Equipo de cómputo no encontrado', 404);
        }

        $templatePath = storage_path('app/templates/plantilla_hoja_vida_equipos.xlsx');
        if (!file_exists($templatePath)) {
            throw new Exception('No se encontró la plantilla de hoja de vida.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Datos básicos
        $sheet->setCellValue('B6', $sheet->getCell('B6')->getValue() . ' ' . ($equipo->nombre_equipo ?? ''));
        $sheet->setCellValue('H6', $sheet->getCell('H6')->getValue() . ' ' . ($equipo->numero_inventario ?? ''));
        $sheet->setCellValue('B7', $sheet->getCell('B7')->getValue() . ' ' . ($equipo->descripcion_general ?? ''));
        $sheet->setCellValue('B8', $sheet->getCell('B8')->getValue() . ' ' . ($equipo->marca ?? ''));
        $sheet->setCellValue('H8', $sheet->getCell('H8')->getValue() . ' ' . ($equipo->modelo ?? ''));
        $sheet->setCellValue('B9', $sheet->getCell('B9')->getValue() . ' ' . ($equipo->serial ?? ''));
        $sheet->setCellValue('H9', $sheet->getCell('H9')->getValue() . ' ' . optional($equipo->sede)->nombre);
        $sheet->setCellValue('B10', $sheet->getCell('B10')->getValue() . ' ' . ($equipo->tipo ?? ''));
        $sheet->setCellValue('H10', $sheet->getCell('H10')->getValue() . ' '); 
        $sheet->setCellValue('B11', $sheet->getCell('B11')->getValue() . ' ' . optional($equipo->area)->nombre);
        $sheet->setCellValue('H11', $sheet->getCell('H11')->getValue() . ' ' . ($equipo->estado ?? ''));
        $sheet->setCellValue('B12', $sheet->getCell('B12')->getValue() . ' ' . ($equipo->garantia_meses ? $equipo->garantia_meses . ' meses' : ''));
        $sheet->setCellValue('H12', $sheet->getCell('H12')->getValue() . ' ' . optional($equipo->responsable)->nombre);

        // 2. Imagen
        $imagePath = preg_replace('/^storage\//', '', $equipo->imagen_url ?? '');
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            $drawing = new Drawing();
            $drawing->setName('Imagen Equipo');
            $drawing->setDescription('Imagen Equipo');
            $drawing->setPath(storage_path('app/public/' . $imagePath));
            $drawing->setCoordinates('K6');
            $drawing->setHeight(100); 
            $drawing->setWorksheet($sheet);
        }

        // 3. Características Técnicas
        $tec = $equipo->caracteristicasTecnicas;
        if ($tec) {
            $sheet->setCellValue('B14', $sheet->getCell('B14')->getValue() . ' ' . ($tec->procesador ?? ''));
            $sheet->setCellValue('E14', $sheet->getCell('E14')->getValue() . ' ' . trim(($tec->disco_duro ?? '') . ' ' . ($tec->capacidad_disco ?? '')));
            $sheet->setCellValue('H14', $sheet->getCell('H14')->getValue() . ' ' . ($tec->tarjeta_red ?? ''));
            
            $monitor = optional($tec->monitorInventario)->nombre ?? $tec->monitor ?? '';
            $sheet->setCellValue('K14', $sheet->getCell('K14')->getValue() . ' ' . $monitor);
            
            $sheet->setCellValue('B15', $sheet->getCell('B15')->getValue() . ' ' . ($equipo->ip_fija ?? ''));
            $sheet->setCellValue('E15', $sheet->getCell('E15')->getValue() . ' ' . ($tec->usb ?? ''));
            $sheet->setCellValue('H15', $sheet->getCell('H15')->getValue() . ' ' . ($tec->tarjeta_sonido ?? ''));
            
            $teclado = optional($tec->tecladoInventario)->nombre ?? $tec->teclado ?? '';
            $sheet->setCellValue('K15', $sheet->getCell('K15')->getValue() . ' ' . $teclado);
            
            $sheet->setCellValue('B16', $sheet->getCell('B16')->getValue() . ' ' . ($tec->velocidad_red ?? ''));
            $sheet->setCellValue('E16', $sheet->getCell('E16')->getValue() . ' ' . ($tec->unidad_cd ?? ''));
            $sheet->setCellValue('H16', $sheet->getCell('H16')->getValue() . ' ' . ($tec->parlantes ?? ''));
            
            $mouse = optional($tec->mouseInventario)->nombre ?? $tec->mouse ?? '';
            $sheet->setCellValue('K16', $sheet->getCell('K16')->getValue() . ' ' . $mouse);
            
            $sheet->setCellValue('B17', $sheet->getCell('B17')->getValue() . ' ' . ($tec->memoria_ram ?? ''));
            $sheet->setCellValue('E17', $sheet->getCell('E17')->getValue() . ' ' . ($tec->tarjeta_video ?? ''));
            $sheet->setCellValue('H17', $sheet->getCell('H17')->getValue() . ' ' . ($tec->drive ?? ''));
            $sheet->setCellValue('K17', $sheet->getCell('K17')->getValue() . ' ' . ($tec->internet ?? ''));
        } else {
            $sheet->setCellValue('B16', $sheet->getCell('B16')->getValue() . ' ' . ($equipo->ip_fija ?? ''));
        }

        // 4. Forma de Adquisición
        $forma = strtoupper(trim($equipo->forma_adquisicion ?? ''));
        if ($forma === 'COMPRA DIRECTA' || $forma === 'COMPRA') {
            $sheet->setCellValue('D19', 'X');
        } elseif ($forma === 'ALQUILER') {
            $sheet->setCellValue('G19', 'X');
        } elseif ($forma === 'DONACION') {
            $sheet->setCellValue('J19', 'X');
        } elseif ($forma === 'COMODATO') {
            $sheet->setCellValue('M19', 'X');
        }

        // 5. Textos largos
        $sheet->setCellValue('B20', $sheet->getCell('B20')->getValue() . " \n" . ($equipo->repuestos_principales ?? ''));
        $sheet->setCellValue('B22', $sheet->getCell('B22')->getValue() . " \n" . ($equipo->equipos_adicionales ?? ''));
        $sheet->setCellValue('B24', $sheet->getCell('B24')->getValue() . " \n" . ($equipo->recomendaciones ?? ''));
        $sheet->setCellValue('B26', $sheet->getCell('B26')->getValue() . " \n" . ($equipo->observaciones ?? ''));
        
        $fechaEntrega = $equipo->fecha_entrega ? $equipo->fecha_entrega->format('d/m/Y') : '';
        $sheet->setCellValue('B28', $sheet->getCell('B28')->getValue() . ' ' . $fechaEntrega);

        // Guardar y retornar
        $fileName = 'hoja_vida_equipo_' . $equipo->id . '_' . time() . '.xlsx';
        $exportPath = storage_path('app/public/exports/' . $fileName);
        
        if (!file_exists(storage_path('app/public/exports'))) {
            mkdir(storage_path('app/public/exports'), 0777, true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($exportPath);
        
        $spreadsheet->disconnectWorksheets();

        return $fileName;
    }
}

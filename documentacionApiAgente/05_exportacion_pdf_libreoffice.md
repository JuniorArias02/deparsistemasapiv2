# Guía y Patrones para Exportación a PDF mediante LibreOffice y PhpSpreadsheet

En NexaCore, la generación de PDFs complejos (como actas o formatos institucionales) se realiza mediante un flujo de dos pasos:
1. **Generación de Excel:** Se utiliza `PhpSpreadsheet` para cargar una plantilla preexistente (`.xlsx`) y rellenar las celdas o incrustar imágenes (como firmas).
2. **Conversión a PDF:** El archivo Excel temporal generado se envía a un **microservicio externo** que internamente utiliza **LibreOffice Headless** (`soffice`) para convertir el documento a formato PDF y devolverlo a la API de Laravel.

Este enfoque asegura un diseño pixel-perfect basado en las plantillas de Excel, pero introduce posibles puntos de fallo al interactuar con el motor de LibreOffice.

---

## 🛑 Errores Comunes de LibreOffice y sus Causas

Cuando la conversión falla en el microservicio, es común observar los siguientes errores en los logs:

### 1. `SfxBaseModel::impl_store failed: 0x909 (Error Area:Io Class:Space Code:9)` o `0xc10 (Class:Write Code:16)`

Este error indica que LibreOffice intentó abrir o procesar el archivo `.xlsx`, pero falló catastróficamente al interpretarlo o intentar escribir el resultado temporal. Las causas más comunes en nuestra arquitectura son:

- **Eliminación Insegura de Hojas (Sheet Deletion):**
  A menudo, intentamos usar `$spreadsheet->removeSheetByIndex(...)` para borrar hojas adicionales y evitar que el PDF salga con páginas en blanco. Sin embargo, **si la hoja eliminada contenía referencias, nombres definidos, áreas de impresión ocultas o macros**, el archivo resultante quedará en un estado que Excel puede "tolerar" pero LibreOffice considerará **corrupto**. LibreOffice abortará la conversión lanzando este error.
  *Solución:* Evita eliminar hojas a menos que estés 100% seguro de que no tienen referencias cruzadas. Es preferible configurar correctamente el "Área de impresión" desde el archivo original de la plantilla.

- **Imágenes o Firmas Corruptas:**
  Si incrustamos una imagen (como las firmas base64 de SignaturePad) y el archivo resultante tiene problemas de metadatos o la imagen no es un formato estrictamente válido, LibreOffice puede crashear al intentar renderizar el documento.
  *Solución:* Asegúrate de que las imágenes (firmas) se hayan decodificado correctamente, existan físicamente en el disco (mediante el `Storage::disk('public')->exists()`) y no pesen demasiado.

- **Bloqueo del Archivo (File Lock) o Problemas de Permisos:**
  Si el microservicio intenta procesar múltiples archivos concurrentes con el mismo nombre en la carpeta Temp de Windows, o si el disco se llena.
  *Solución:* Asegúrate de enviar siempre archivos con nombres únicos (ej. añadiendo `time()` o `uniqid()` o usando `tempnam()`) y asegúrate de cerrar la conexión del PhpSpreadsheet antes de enviarlo: `$spreadsheet->disconnectWorksheets();`.

---

## 📝 Patrón Recomendado para Casos de Uso (Exportación PDF)

Cada vez que crees un caso de uso para exportar a PDF (ej. `ExportarDocumentoPdfUseCase`), debes seguir estrictamente esta estructura arquitectónica y bloque de rescate (try-catch) para asegurar la integridad de la memoria y la conversión.

```php
<?php

namespace App\Modules\Modulo\Application\UseCases;

use App\Modules\Shared\Domain\Contracts\ExcelToPdfConverterInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Exception;

class ExportarDocumentoPdfUseCase
{
    // 1. Inyectar únicamente el convertidor (los datos se pueden buscar adentro mediante Eloquent directo o un repositorio local al caso)
    public function __construct(
        protected ExcelToPdfConverterInterface $pdfConverter
    ) {}

    public function execute(int $id): string
    {
        // 2. Obtener la entidad con todas las relaciones necesarias
        $entidad = \App\Models\Entidad::with([...])->findOrFail($id);

        // 3. Cargar la plantilla Excel
        $templatePath = storage_path('app/templates/plantilla_documento.xlsx');
        if (!file_exists($templatePath)) {
            throw new Exception('No se encontró la plantilla.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // 4. Llenar la información en las celdas
        $sheet->setCellValue('A1', $entidad->campo);
        
        // 5. Incrustar imágenes (Firmas) verificando siempre su existencia previa
        // (Usando PhpOffice\PhpSpreadsheet\Worksheet\Drawing)

        // IMPORTANTE: Evitar eliminar hojas ($spreadsheet->removeSheetByIndex) si no es estrictamente necesario y probado.
        
        // 6. Preparar el archivo temporal para LibreOffice
        $filename = 'documento_' . $entidad->id . '_' . time() . '.pdf';
        $tempExcelPath = tempnam(sys_get_temp_dir(), 'doc_excel_') . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempExcelPath);
        
        // Desconectar para liberar memoria y posibles bloqueos de archivo
        $spreadsheet->disconnectWorksheets();

        try {
            // 7. Enviar al microservicio convertidor
            $pdfContent = $this->pdfConverter->convert($tempExcelPath);
            
            // 8. Eliminar archivo temporal inmediatamente
            @unlink($tempExcelPath);

            // 9. Guardar PDF resultante en public
            $exportDir = storage_path('app/public/exports');
            if (!file_exists($exportDir)) {
                mkdir($exportDir, 0777, true);
            }

            $exportPath = $exportDir . '/' . $filename;
            file_put_contents($exportPath, $pdfContent);

            return $filename;

        } catch (Exception $e) {
            // En caso de fallo en el microservicio, limpiar la basura temporal
            @unlink($tempExcelPath);
            throw $e;
        }
    }
}
```

### 💡 Puntos Claves al Implementar:
1. **Delegación de Responsabilidad:** El caso de uso prepara el archivo temporal pero NO realiza la conversión pesada, inyecta `ExcelToPdfConverterInterface`.
2. **Sanidad de la Plantilla:** Revisa que el documento `.xlsx` que uses de plantilla tenga configurada el **Área de impresión** adecuadamente desde Microsoft Excel antes de subirlo al servidor; esto evita tener páginas en blanco de PDF sin necesidad de programar eliminación de hojas.
3. **Limpieza Garantizada:** El bloque `try-catch` con `@unlink($tempExcelPath)` garantiza que el servidor de Laravel no se sature de archivos temporales huérfanos cuando la API de Node.js o LibreOffice colapsa.

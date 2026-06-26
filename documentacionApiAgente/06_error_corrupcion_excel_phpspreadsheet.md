# Resolución de Error: Corrupción de Excel con PhpSpreadsheet

Este documento registra la solución a un problema crítico de compatibilidad detectado al exportar archivos Excel utilizando la librería `PhpSpreadsheet` y abriéndolos posteriormente en **Microsoft Excel**.

## 1. Descripción del Problema

Al intentar abrir un archivo `.xlsx` exportado desde el sistema utilizando Microsoft Excel, se presentaba una ventana emergente de error con el siguiente mensaje:

> **"Reparaciones en [NombreDelArchivo].xlsx"**
> Excel pudo abrir el archivo reparando o quitando el contenido que no se podía leer.
> Excel ha completado la validación y reparación en nivel de archivo. Puede que se hayan reparado o descartado algunas partes de este libro.
> **Parte quitada: Parte /xl/drawings/drawing1.xml. (Forma de dibujo)**

Como consecuencia de esta "reparación", Microsoft Excel eliminaba todas las imágenes del documento (incluyendo el logo de la plantilla y las firmas insertadas).

**Curiosidad del error:** Si el mismo archivo se abría en *LibreOffice Calc*, el documento se visualizaba perfectamente sin arrojar ningún error de integridad.

## 2. Origen y Contexto

El problema empezó a ocurrir coincidentemente cuando se añadió el módulo de **Exportación a PDF** para los pedidos. 

Para que el PDF exportado (usando LibreOffice en el backend) no mostrara páginas en blanco al final, se implementó un fragmento de código que eliminaba hojas adicionales (`getSheetCount() > 1`) de la plantilla base:

```php
// Código problemático para exportar Excel
while ($spreadsheet->getSheetCount() > 1) {
    $activeIndex = $spreadsheet->getActiveSheetIndex();
    $indexToRemove = $activeIndex === 0 ? 1 : 0;
    $spreadsheet->removeSheetByIndex($indexToRemove);
}
```

Este fragmento fue útil para el PDF, pero también se dejó activo en el Caso de Uso para generar Excel (`ExportarPedidoExcelUseCase.php`).

## 3. Causa Técnica (Bug de PhpSpreadsheet)

El problema radica en una deficiencia conocida de la librería **PhpSpreadsheet**. 

Cuando se utiliza `$spreadsheet->removeSheetByIndex(...)` en un documento que contiene imágenes o firmas (ya sea en la hoja eliminada o en la principal), PhpSpreadsheet a menudo **corrompe las relaciones internas (relationships)** del archivo XML comprimido (`drawing1.xml` o los archivos `_rels`). 

- **LibreOffice:** Tiene un parser tolerante que ignora las referencias XML "sucias" o rotas, logrando renderizar las imágenes de todas formas.
- **Microsoft Excel:** Aplica una validación estricta al estándar OOXML. Al notar que las referencias del dibujo tienen inconsistencias (causadas por la hoja borrada), determina que el archivo XML del dibujo está corrupto y decide **eliminar por completo el archivo `drawing1.xml`** para poder abrir el libro de forma segura.

## 4. Solución Implementada

La solución consistió en separar la lógica del manejo de hojas entre el exportador de Excel y el exportador de PDF.

En el archivo de exportación de Excel (`ExportarPedidoExcelUseCase.php`), **se retiró por completo la eliminación de las hojas extra**. 

Para un archivo Excel, dejar las hojas auxiliares (ocultas o vacías) que provengan de la plantilla no afecta de manera negativa la experiencia del usuario final, y lo más importante: **evita la corrupción del archivo**. 

Para la exportación de PDF (`ExportarPedidoPdfUseCase.php`), la lógica de eliminar hojas se mantiene y el PDF se renderiza correctamente porque no pasa por el motor estricto de validación de Microsoft Office.

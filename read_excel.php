<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('storage/app/templates/plantilla_acta_entrega_equipos.xlsx');
$worksheet = $spreadsheet->getActiveSheet();

$cells = [];
foreach ($worksheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false); 
    foreach ($cellIterator as $cell) {
        $val = $cell->getValue();
        if ($val) {
            echo $cell->getCoordinate() . " => " . $val . "\n";
        }
    }
}

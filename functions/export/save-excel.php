<?php
session_start();
require_once '../../config/config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

header('Content-Type: application/json');  // Imposta l'header per la risposta JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $modello = $data['modello'];
    $lancio = $data['lancio'];
    $qty = $data['qty'];
    $tableTaglio = $data['tableTaglio'];
    $tableOrlatura = $data['tableOrlatura'];

    require_once BASE_PATH . '/vendor/autoload.php';

    $spreadsheet = new Spreadsheet();
    $spreadsheet->getProperties()
        ->setCreator("Calzaturificio Emmegiemme Shoes Srl")
        ->setLastModifiedBy("Calzaturificio Emmegiemme Shoes Srl")
        ->setTitle("Scheda Tecnica")
        ->setCategory("Excel");

    // Add data to the sheet
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('SCHEDA TECNICA');

    $sheet->setCellValue('A1', 'ARTICOLO:');
    $sheet->setCellValue('B1', $modello);
    $sheet->setCellValue('A2', 'LANCIO:');
    $sheet->setCellValue('B2', $lancio);
    $sheet->setCellValue('A3', 'PAIA DA PRODURRE:');
    $sheet->setCellValue('B3', $qty);

    $sheet->setCellValue('A5', 'TIPO');
    $sheet->setCellValue('B5', 'CODICE');
    $sheet->setCellValue('C5', 'DESCRIZIONE');
    $sheet->setCellValue('D5', 'UM');
    $sheet->setCellValue('E5', 'CONS/PA');
    $sheet->setCellValue('F5', 'TOTALE');

    // Add the "TAGLIO" row before the TAGLIO table
    $sheet->insertNewRowBefore(6);
    $sheet->setCellValue('A6', 'TAGLIO');
    $sheet->getStyle('A6:F6')
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('A6:F6')
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('e6f5fa');
    $sheet->getStyle('A6:F6')
        ->getFont()
        ->setBold(true)
        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK));

    $rowIndex = 7;
    foreach ($tableTaglio as $row) {
        $colIndex = 1;
        foreach ($row as $cell) {
            $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $cell);
            $colIndex++;
        }
        $rowIndex++;
    }

    // Add the "ORLATURA" row
    $sheet->setCellValue('A' . $rowIndex, 'ORLATURA');
    $sheet->getStyle('A' . $rowIndex . ':F' . $rowIndex)
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('A' . $rowIndex . ':F' . $rowIndex)
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('e6f5fa');
    $sheet->getStyle('A' . $rowIndex . ':F' . $rowIndex)
        ->getFont()
        ->setBold(true)
        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK));

    $rowIndex++;

    foreach ($tableOrlatura as $row) {
        $colIndex = 1;
        foreach ($row as $cell) {
            $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $cell);
            $colIndex++;
        }
        $rowIndex++;
    }

    // Remove the 7th column
    $sheet->removeColumn('G');

    $filename = "temp/{$modello}.xlsx";
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);

    echo json_encode(['success' => true, 'filename' => $filename]);

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>

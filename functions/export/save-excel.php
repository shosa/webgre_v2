<?php
session_start();
require_once '../../config/config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

header('Content-Type: application/json');  // Imposta l'header per la risposta JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $modello = $data['modello'];
    $lancio = $data['lancio'];
    $qty = $data['qty'];
    $tableTaglio = $data['tableTaglio'];
    $tableOrlatura = $data['tableOrlatura'];
    $id_documento = isset($data['id_documento']) ? $data['id_documento'] : null;

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

    // Recupera l'autorizzazione se abbiamo un ID documento
    $autorizzazione = "";
    if ($id_documento) {
        try {
            $conn = getDbInstance();
            $stmt = $conn->prepare("SELECT autorizzazione FROM exp_piede_documenti WHERE id_documento = :id_documento");
            $stmt->bindParam(':id_documento', $id_documento, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && isset($result['autorizzazione'])) {
                $autorizzazione = $result['autorizzazione'];
            }
        } catch (PDOException $e) {
            // Log error or handle it
            error_log("Errore nel recupero dell'autorizzazione: " . $e->getMessage());
        }
    }

    // Lascia una riga vuota dopo la tabella
    $rowIndex += 2;
    
    // Aggiungi l'autorizzazione al foglio
    if (!empty($autorizzazione)) {
        $sheet->setCellValue('A' . $rowIndex, 'AUTORIZZAZIONE:');
        $sheet->getStyle('A' . $rowIndex)
            ->getFont()
            ->setBold(true);
        
        // Unisci le celle per l'autorizzazione che può essere lunga
        $sheet->mergeCells('B' . $rowIndex . ':F' . $rowIndex);
        $sheet->setCellValue('B' . $rowIndex, $autorizzazione);
        
        // Imposta lo stile per le celle dell'autorizzazione
        $sheet->getStyle('A' . $rowIndex . ':F' . $rowIndex)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
            
        $sheet->getStyle('B' . $rowIndex)
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_TOP);
            
        // Imposta l'altezza della riga in base al contenuto
        $sheet->getRowDimension($rowIndex)->setRowHeight(-1);
    }

    // Remove the 7th column if exists
    if ($sheet->getHighestColumn() === 'G') {
        $sheet->removeColumn('G');
    }

    // Autosize columns
    foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $filename = "temp/{$modello}.xlsx";
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);

    echo json_encode(['success' => true, 'filename' => $filename]);

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
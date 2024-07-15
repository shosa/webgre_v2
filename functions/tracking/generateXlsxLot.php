<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/log_utils.php';
require_once BASE_PATH . '/vendor/autoload.php'; // Percorso all'autoloader di PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Leggi il payload JSON inviato dal client
$postdata = file_get_contents("php://input");
$request = json_decode($postdata, true);

// Verifica se i lotti sono stati ricevuti correttamente
if (isset($request['lotti'])) {
    $lotti = $request['lotti'];

    try {
        // Connessione al database
        $db = getDbInstance();

        // Preparazione della query per ottenere i dati
        $placeholders = rtrim(str_repeat('?, ', count($lotti)), ', ');
        $query = "
            SELECT 
                d.`Descrizione Articolo`,
                d.`Commessa Cli`,
                tl.cartel, 
                tt.name AS type_name, 
                tl.lot
            FROM track_links tl
            JOIN track_types tt ON tl.type_id = tt.id
            JOIN dati d ON d.cartel = tl.cartel
            WHERE tl.lot IN ($placeholders) ORDER BY Cartel ASC";
        $stmt = $db->prepare($query);
        $stmt->execute($lotti);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Raggruppamento dei risultati per 'Descrizione Articolo', 'type_name', 'lot'
        $groupedResults = [];
        foreach ($results as $row) {
            $groupedResults[$row['Descrizione Articolo']][$row['type_name']][$row['lot']][] = [
                'cartel' => $row['cartel'],
                'commessa' => $row['Commessa Cli']
            ];
        }

        // Creazione del documento Excel con PhpSpreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Intestazione del foglio
        $sheet->setCellValue('A1', 'Descrizione Articolo');
        $sheet->setCellValue('B1', 'Tipo');
        $sheet->setCellValue('C1', 'Lotto');
        $sheet->setCellValue('D1', 'Cartellino');
        $sheet->setCellValue('E1', 'Commessa');

        // Inserimento dei dati raggruppati nel foglio
        $row = 2;
        foreach ($groupedResults as $descrizioneArticolo => $tipi) {
            foreach ($tipi as $type_name => $lotti) {
                foreach ($lotti as $lot => $details) {
                    foreach ($details as $detail) {
                        $sheet->setCellValue('A' . $row, $descrizioneArticolo);
                        $sheet->setCellValue('B' . $row, $type_name);
                        $sheet->setCellValue('C' . $row, $lot);
                        $sheet->setCellValue('D' . $row, $detail['cartel']);
                        $sheet->setCellValue('E' . $row, $detail['commessa']);
                        $row++;
                    }
                }
            }
        }

        // Impostazione del nome del file e del tipo di output
        $filename = 'report.xlsx';

        // Impostazione degli header per il download del file XLSX
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Salvataggio del file XLSX
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;

    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array('message' => 'Errore del server: ' . $e->getMessage()));
        exit;
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array('message' => 'Dati non ricevuti correttamente.'));
    exit;
}
?>

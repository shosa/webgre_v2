<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/log_utils.php';
require_once BASE_PATH . '/vendor/autoload.php'; // Path to PhpSpreadsheet autoload file

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Leggi il payload JSON inviato dal client
$postdata = file_get_contents("php://input");
$request = json_decode($postdata, true);

// Verifica se cartellini è stato ricevuto correttamente
if (isset($request['cartellini'])) {
    $cartellini = $request['cartellini'];

    try {
        // Connessione al database
        $db = getDbInstance();

        // Preparazione della query per ottenere i dati
        $placeholders = rtrim(str_repeat('?, ', count($cartellini)), ', ');
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
            WHERE tl.cartel IN ($placeholders) ORDER BY Cartel ASC";
        $stmt = $db->prepare($query);
        $stmt->execute($cartellini);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Raggruppamento dei risultati per 'Descrizione Articolo', 'cartel', 'Commessa Cli', 'type_name' e 'lot'
        $groupedResults = [];
        foreach ($results as $row) {
            $groupedResults[$row['Descrizione Articolo']][$row['Commessa Cli']][$row['cartel']][$row['type_name']][] = [
                'lot' => $row['lot'],
            ];
        }

        // Creazione di un nuovo foglio di calcolo
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Intestazione del foglio di calcolo
        $sheet->setCellValue('A1', 'Descrizione Articolo');
        $sheet->setCellValue('B1', 'Commessa Cli');
        $sheet->setCellValue('C1', 'Cartellino');
        $sheet->setCellValue('D1', 'Tipo Articolo');
        $sheet->setCellValue('E1', 'Lotto');

        // Popolamento del foglio di calcolo con i dati raggruppati
        $row = 2;
        foreach ($groupedResults as $descrizioneArticolo => $commesse) {
            foreach ($commesse as $commessa => $cartellini) {
                foreach ($cartellini as $cartel => $types) {
                    foreach ($types as $type_name => $lots) {
                        foreach ($lots as $lot) {
                            $sheet->setCellValue('A' . $row, $descrizioneArticolo);
                            $sheet->setCellValue('B' . $row, $commessa);
                            $sheet->setCellValue('C' . $row, $cartel);
                            $sheet->setCellValue('D' . $row, $type_name);
                            $sheet->setCellValue('E' . $row, $lot['lot']);
                            $row++;
                        }
                    }
                }
            }
        }

        // Impostazioni di formattazione (opzionale)
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        // Creazione del writer per il file XLSX
        $xlsxWriter = new Xlsx($spreadsheet);

        // Salvataggio del file XLSX
        $tempFile = tempnam(sys_get_temp_dir(), 'packing_list_');
        $xlsxWriter->save($tempFile);

        // Invio del file al client
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="packing_list.xlsx"');
        header('Cache-Control: max-age=0');

        readfile($tempFile);

        // Rimozione del file temporaneo
        unlink($tempFile);

    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array('message' => 'Errore del server: ' . $e->getMessage()));
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array('message' => 'Dati non ricevuti correttamente.'));
}
?>
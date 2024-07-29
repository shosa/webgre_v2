<?php

session_start();

require_once '../../config/config.php';
require_once BASE_PATH . '/utils/log_utils.php';
require_once BASE_PATH . '/vendor/autoload.php'; // Path to PhpSpreadsheet autoload file

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

// Leggi il payload JSON inviato dal client
$postdata = file_get_contents("php://input");
$request = json_decode($postdata, true);

// Verifica se cartellini è stato ricevuto correttamente
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
                d.`Tot`,
                d.cartel, 
                tl.lot, 
                tt.name AS type_name,
                to_info.date AS data_inserimento,
                s.sku AS codice_articolo
            FROM track_links tl
            JOIN track_types tt ON tl.type_id = tt.id
            JOIN dati d ON d.cartel = tl.cartel
            LEFT JOIN track_order_info to_info ON to_info.ordine = d.`Ordine`
            LEFT JOIN track_sku s ON s.art = d.`Articolo`
            WHERE tl.lot IN ($placeholders)
            ORDER BY d.cartel ASC";

        $stmt = $db->prepare($query);
        $stmt->execute($lotti);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Raggruppamento dei risultati per cartellino
        $groupedResults = [];
        foreach ($results as $row) {
            $cartel = $row['cartel'];
            if (!isset($groupedResults[$cartel])) {
                $groupedResults[$cartel] = [
                    'data_inserimento' => $row['data_inserimento'],
                    'riferimento_originale' => $row['Commessa Cli'],
                    'codice_articolo' => $row['codice_articolo'],
                    'paia' => $row['Tot'],
                    'types' => []
                ];
            }
            $groupedResults[$cartel]['types'][$row['type_name']][] = $row['lot'];
        }

        // Creazione di un nuovo foglio di calcolo
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Intestazione del foglio di calcolo
        $sheet->setCellValue('A1', 'Data Inserimento');
        $sheet->setCellValue('B1', 'Riferimento Originale');
        $sheet->setCellValue('C1', 'Codice Articolo');
        $sheet->setCellValue('D1', 'Paia');

        // Creazione delle colonne per ogni tipo di articolo
        $typeColumnStart = 'E'; // Start after 'D'
        $typeColumns = [];
        $columnIndex = ord($typeColumnStart);

        // Prendi tutti i nomi dei tipi unici
        $typeNames = array_unique(array_merge(...array_map(function ($v) {
            return array_keys($v['types']);
        }, $groupedResults)));

        foreach ($typeNames as $typeName) {
            $typeColumn = chr($columnIndex++);
            $sheet->setCellValue($typeColumn . '1', $typeName);
            $typeColumns[$typeName] = $typeColumn;
        }

        // Popolamento del foglio di calcolo con i dati raggruppati
        $row = 2;
        foreach ($groupedResults as $cartel => $data) {
            $sheet->setCellValue('A' . $row, $data['data_inserimento']);
            $sheet->setCellValue('B' . $row, $data['riferimento_originale']);
            $sheet->setCellValue('C' . $row, $data['codice_articolo']);
            $sheet->setCellValue('D' . $row, $data['paia']);

            foreach ($data['types'] as $typeName => $lots) {
                $column = $typeColumns[$typeName] ?? null;
                if ($column) {
                    // Imposta il formato della cella come testo per evitare la visualizzazione in formato scientifico
                    foreach ($lots as $index => $lot) {
                        $sheet->setCellValueExplicit($column . $row, $lot, DataType::TYPE_STRING);
                    }
                }
            }

            $row++;
        }

        // Impostazioni di formattazione (opzionale)
        $sheet->getStyle('A1:' . chr($columnIndex - 1) . '1')->getFont()->setBold(true);

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
        echo json_encode(['message' => 'Errore del server: ' . $e->getMessage()]);
    }

} else {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Dati non ricevuti correttamente.']);
}

?>
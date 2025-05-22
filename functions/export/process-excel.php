<?php
require_once '../../config/config.php';

$fileName = $_GET['fileName'];
$filePath = 'uploads/' . $fileName;

// Funzione per sanitizzare il testo lungo
function sanitizeText($text) {
    if ($text === null) return '';
    
    // Converti in stringa se non lo è già
    $text = (string) $text;
    
    // Rimuovi caratteri di controllo e non stampabili
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
    
    // Limita la lunghezza per evitare problemi di memoria
    if (strlen($text) > 500) {
        $text = substr($text, 0, 497) . '...';
    }
    
    // Escape caratteri problematici per JSON
    $text = str_replace(['"', "'", '\\'], ['\"', "\'", '\\\\'], $text);
    
    return trim($text);
}

if (file_exists($filePath)) {
    require_once BASE_PATH . '/vendor/autoload.php';

    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();

    $headers = [];
    $rows = [
        'taglio' => [],
        'orlatura' => []
    ];

    $isTaglio = false;
    $isOrlatura = false;
    $modello = '';

    foreach ($worksheet->getRowIterator() as $index => $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(FALSE);

        $rowData = [];

        foreach ($cellIterator as $cell) {
            $value = $cell->getValue();
            // SANITIZZA OGNI VALORE DELLA CELLA
            $rowData[] = sanitizeText($value);
        }

        if ($index == 1) {
            $modello = isset($rowData[1]) ? sanitizeText($rowData[1]) : '';
            continue;
        }

        if (isset($rowData[0]) && $rowData[0] == "02 - 1 TAGLIO") {
            $isTaglio = true;
            $isOrlatura = false;
            continue;
        }

        if (isset($rowData[0]) && $rowData[0] == "04 - 1 ORLATURA") {
            $isOrlatura = true;
            $isTaglio = false;
            continue;
        }

        if (count($rowData) > 0) {
            $colonnaA = isset($rowData[0]) ? trim($rowData[0]) : '';
            $colonnaB = isset($rowData[1]) ? trim($rowData[1]) : '';
            
            if (empty($colonnaA)) {
                if (!empty($colonnaB)) {
                    $rowData[0] = "ALTRO";
                } else {
                    continue;
                }
            }

            // Assicurati che l'array abbia esattamente 5 elementi
            while (count($rowData) < 5) {
                $rowData[] = '';
            }

            $processedRow = array_slice($rowData, 0, 5);
            
            // Ulteriore sanitizzazione per sicurezza
            foreach ($processedRow as $key => $value) {
                $processedRow[$key] = sanitizeText($value);
            }
            
            $hasContent = false;
            foreach ($processedRow as $cell) {
                if (!empty(trim($cell))) {
                    $hasContent = true;
                    break;
                }
            }
            
            if ($hasContent) {
                if ($isTaglio && !$isOrlatura) {
                    $rows['taglio'][] = $processedRow;
                }

                if ($isOrlatura) {
                    $rows['orlatura'][] = $processedRow;
                }
            }
        }
    }

    // Get headers from the 6th row
    $headerRow = $worksheet->getRowIterator(6)->current();
    if ($headerRow !== null) {
        $cellIterator = $headerRow->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(FALSE);

        foreach ($cellIterator as $cell) {
            $value = $cell->getValue();
            $headers[] = sanitizeText($value);
        }

        $headers = array_slice($headers, 0, 5);
        while (count($headers) < 5) {
            $headers[] = '';
        }
    } else {
        $headers = ['Colonna 1', 'Colonna 2', 'Colonna 3', 'Colonna 4', 'Colonna 5'];
    }

    $response = [
        'modello' => $modello,
        'headers' => $headers,
        'rows' => $rows
    ];

    // Imposta header per JSON con encoding UTF-8
    header('Content-Type: application/json; charset=utf-8');
    
    // Usa JSON_UNESCAPED_UNICODE per gestire meglio i caratteri speciali
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS);
    
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'File not found'], JSON_UNESCAPED_UNICODE);
}
?>
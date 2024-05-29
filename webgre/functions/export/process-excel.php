<?php
require_once '../../config/config.php';

$fileName = $_GET['fileName'];
$filePath = 'uploads/' . $fileName;

if (file_exists($filePath)) {
    require_once BASE_PATH . '/vendor/autoload.php';

    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();

    $headers = [];
    $rows = [];

    $isTaglio = false;
    $isOrlatura = false;
    $modello = '';

    foreach ($worksheet->getRowIterator() as $index => $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(FALSE);

        $rowData = [];

        foreach ($cellIterator as $cell) {
            $rowData[] = $cell->getValue();
        }

        if ($index == 1) {
            $modello = $rowData[1];
            continue;
        }

        if ($rowData[0] == "02 - 1 TAGLIO") {
            $isTaglio = true;
            $isOrlatura = false;
            continue;
        }

        if ($rowData[0] == "04 - 1 ORLATURA") {
            $isOrlatura = true;
            $isTaglio = false;
            continue;
        }

        if (count($rowData) > 0 && $rowData[0] == null) {
            continue;
        }

        if ($isTaglio && !$isOrlatura) {
            $rows['taglio'][] = array_slice($rowData, 0, 5);  // Get only the first 5 columns
        }

        if ($isOrlatura) {
            $rows['orlatura'][] = array_slice($rowData, 0, 5);  // Get only the first 5 columns
        }
    }

    // Get headers from the 6th row
    $row = $worksheet->getRowIterator(6)->current();
    if ($row !== null) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(FALSE);

        foreach ($cellIterator as $cell) {
            $headers[] = $cell->getValue();
        }

        // Get only the first 5 headers
        $headers = array_slice($headers, 0, 5);
    }

    echo json_encode(['modello' => $modello, 'headers' => $headers, 'rows' => $rows]);
} else {
    echo json_encode(['error' => 'File not found']);
}
?>
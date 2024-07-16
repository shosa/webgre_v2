<?php
require_once '../../config/config.php';
require_once '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function fetchTrackLinks()
{
    $pdo = getDbInstance();

    // Fetch all records from track_links
    $sql = "SELECT tl.*, dati.`Commessa Cli`, tt.name AS type_name 
            FROM track_links tl 
            LEFT JOIN track_types tt ON tl.type_id = tt.id
            LEFT JOIN dati ON dati.Cartel = tl.cartel";
    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$results = fetchTrackLinks();

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'Cartellino');
$sheet->setCellValue('B1', 'Tipologia');
$sheet->setCellValue('C1', 'Lotto #');
$sheet->setCellValue('D1', 'Registrato il');

// Populate data
$row = 2;
foreach ($results as $result) {
    $sheet->setCellValue('A' . $row, $result['cartel']);
    $sheet->setCellValue('B' . $row, $result['type_name']);
    $sheet->setCellValue('C' . $row, $result['lot']);
    $sheet->setCellValue('D' . $row, $result['timestamp']);
    $row++;
}

// Set header for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Associazioni.xlsx"');
header('Cache-Control: max-age=0');

// Create Excel file and download it
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
<?php session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/log_utils.php';
require_once BASE_PATH . '/vendor/autoload.php'; // Path to PhpSpreadsheet autoload file

$postdata = file_get_contents("php://input");
$request = json_decode($postdata, true);
if (isset($request['cartellini'])) {
    $cartellini = $request['cartellini'];
    try {
        $db = getDbInstance();
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
        $groupedResults = [];
        foreach ($results as $row) {
            $groupedResults[$row['Descrizione Articolo']][$row['Commessa Cli']][$row['cartel']][$row['type_name']][] = ['lot' => $row['lot'],];
        }
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Emmegiemme');
        $pdf->SetTitle('Packing List - Per Cartellino');
        $pdf->SetSubject('Report');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        $pdf->AddPage();
        $pdf->SetCellHeightRatio(0.5);
        $pdf->SetFont('helvetica', '', 15);
        $pdf->SetFillColor(204, 228, 255);
        $pdf->Cell(0, 10, "PACKING LIST - Dettaglio lotti di produzione per Cartellini", 0, 1, 'L', true);
        $pdf->SetFillColor(204, 228, 255);
        foreach ($groupedResults as $descrizioneArticolo => $commesse) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, $descrizioneArticolo, 0, 1, 'L');
            $pdf->Ln(1);
            foreach ($commesse as $commessa => $cartellini) {
                $pdf->SetFont('helvetica', '', 10);
                foreach ($cartellini as $cartel => $types) {
                    $pdf->SetFillColor(240, 240, 240);
                    $pdf->Cell(0, 10, "Cartellino: $cartel / Commessa: $commessa", 0, 1, 'L', true);
                    $colWidth = 65;
                    $pdf->SetFont('helvetica', 'B', 8);
                    foreach ($types as $type_name => $lots) {
                        $pdf->Cell($colWidth, 10, $type_name, 0, 0, 'C', false);
                    }
                    $pdf->Ln();
                    $pdf->SetFont('helvetica', '', 8);
                    $maxRows = 0;
                    foreach ($types as $type_name => $lots) {
                        $rows = count($lots);
                        if ($rows > $maxRows) {
                            $maxRows = $rows;
                        }
                    }
                    for ($row = 0; $row < $maxRows; $row++) {
                        foreach ($types as $type_name => $lots) {
                            if (isset($lots[$row])) {
                                $pdf->Cell($colWidth, 10, $lots[$row]['lot'], 0, 0, 'C');
                            } else {
                                $pdf->Cell($colWidth, 10, '', 0, 0, 'C');
                            }
                        }
                        $pdf->Ln();
                    }
                    $pdf->Ln(2);
                }
                $pdf->Ln(4);
            }
            $pdf->Ln(6);
        }
        $pdfContent = $pdf->Output('', 'S');
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="report.pdf"');
        echo $pdfContent;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array('message' => 'Errore del server: ' . $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array('message' => 'Dati non ricevuti correttamente.'));
} ?>
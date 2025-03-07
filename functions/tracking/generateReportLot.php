<?php session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/log_utils.php';
require_once BASE_PATH . '/vendor/autoload.php';
$postdata = file_get_contents("php://input");
$request = json_decode($postdata, true);
if (isset($request['lotti'])) {
    $lotti = $request['lotti'];
    try {
        $db = getDbInstance();
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
        $groupedResults = [];
        foreach ($results as $row) {
            $groupedResults[$row['Descrizione Articolo']][$row['type_name']][$row['lot']][] = ['cartel' => $row['cartel'], 'commessa' => $row['Commessa Cli']];
        }
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Emmegiemme');
        $pdf->SetTitle('Packing List - Per Lotto');
        $pdf->SetSubject('Report');
        $pdf->SetKeywords('PDF');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        $pdf->AddPage();
        $pdf->SetCellHeightRatio(1.5);
        $coloreSfondo = array(204, 228, 255);
        $coloreIntestazione = array(119, 119, 119);
        $coloreTesto = array(0, 0, 0);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetFillColor($coloreSfondo[0], $coloreSfondo[1], $coloreSfondo[2]);
        $pdf->SetTextColor($coloreTesto[0], $coloreTesto[1], $coloreTesto[2]);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, "PACKING LIST - Dettaglio per Lotto", 0, 1, 'L', true);
        $pdf->Ln(1);
        foreach ($groupedResults as $descrizioneArticolo => $tipi) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 10, "$descrizioneArticolo", 0, 1, 'L', true);
            $pdf->Ln(1);
            foreach ($tipi as $type_name => $lotti) {
                $pdf->SetFont('helvetica', '', 10);
                $pdf->Cell(0, 10, "$type_name", 0, 1, 'L', true);
                $pdf->Ln(1);
                $colWidth = ($pdf->GetPageWidth() - 20) / 3;
                $pdf->SetFont('helvetica', '', 8);
                foreach ($lotti as $lot => $details) {
                    $pdf->Cell($colWidth, 10, "Lotto", 0, 0, 'C', false);
                    $pdf->Cell($colWidth, 10, $lot, 0, 0, 'C', false);
                    $pdf->Cell($colWidth, 10, '', 0, 1, 'C', false);
                    for ($i = 0; $i < count($details); $i += 3) {
                        $pdf->Cell($colWidth, 10, "{$details[$i]['cartel']} / {$details[$i]['commessa']}", 0, 0, 'C');
                        if ($i + 1 < count($details)) {
                            $pdf->Cell($colWidth, 10, "{$details[$i + 1]['cartel']} / {$details[$i + 1]['commessa']}", 0, 0, 'C');
                        } else {
                            $pdf->Cell($colWidth, 10, '', 0, 0, 'C');
                        }
                        if ($i + 2 < count($details)) {
                            $pdf->Cell($colWidth, 10, "{$details[$i + 2]['cartel']} / {$details[$i + 2]['commessa']}", 0, 1, 'C');
                        } else {
                            $pdf->Cell($colWidth, 10, '', 0, 1, 'C');
                        }
                    }
                    $pdf->Ln(4);
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
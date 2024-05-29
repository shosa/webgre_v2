<?php
ob_start();
session_start();
require_once ('../../config/config.php');
require_once (BASE_PATH . '/assets/tcpdf/tcpdf.php');
require_once (BASE_PATH . '/assets/tcpdf/tcpdf_barcodes_1d.php');
include BASE_PATH . '/includes/header-nomenu.php';
require_once BASE_PATH . '/includes/auth_validate.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['barcodes'])) {
    $db = getDbInstance();
    $barcodes = explode("\n", trim($_POST['barcodes']));
    $barcodes = array_filter(array_map('trim', $barcodes));
    $barcodes = array_map(function ($barcode) {
        return str_replace("MGM", "", str_replace("mgm", "", $barcode));
    }, $barcodes);
    $sanitizedBarcodes = array_map(function ($barcode) use ($db) {
        return $db->escape($barcode);
    }, $barcodes);
    $barcodeCounts = array_count_values($barcodes);
    $uniqueBarcodes = array_keys($barcodeCounts);

    $db->where('barcode', $uniqueBarcodes, 'IN');
    $db->groupBy('art');
    $db->groupBy('des');
    $results = $db->get('inv_anagrafiche', null, 'barcode, art, des');
    $azione = isset($_POST['azione']) ? $_POST['azione'] : 'PRELIEVO'; // Predefinito a 'PRELIEVO' se non specificato

    // Determina la dicitura da utilizzare
    $dicitura = $azione === 'VERSAMENTO' ? 'Distinta di Versamento a Magazzino' : 'Distinta di Prelievo di Magazzino';
    if ($results) {
        $pdf = new TCPDF();
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage();

        // Posiziona il logo
        $logoPath = '../../src/img/logo.png'; // Assicurati che il percorso sia corretto
        $pdf->Image($logoPath, 10, 10, 50, '', 'PNG');

        // Posiziona la dicitura "DOCUMENTO INTERNO"
        $pdf->SetXY(60, 15); // Regola queste coordinate come necessario
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 0, 'DOCUMENTO AD USO INTERNO', 0, 1, 'R');

        // Aggiungi titolo e spazio per la data
        $pdf->Ln(15); // Regola lo spazio dopo il logo e la dicitura
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $dicitura, 0, 1, 'L');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Del _____________    ', 0, 1, 'R');
        $pdf->Ln(5); // Regola lo spazio dopo il logo e la dicitura

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetFillColor(240, 240, 240); // Grigio chiaro per le righe alternate
        $fill = false; // Variabile per alternare il riempimento

        // Intestazioni della tabella
        $pdf->Cell(50, 7, 'CODICE', 1, 0, 'L', false);
        $pdf->Cell(120, 7, 'DESCRIZIONE', 1, 0, 'L', false);
        $pdf->Cell(15, 7, 'QTA', 1, 1, 'C', false);

        foreach ($results as $row) {
            $qty = $barcodeCounts[$row['barcode']] ?? 1;
            $pdf->Cell(50, 7, $row['art'], 'LR', 0, 'L', $fill);
            $pdf->Cell(120, 7, $row['des'], 'LR', 0, 'L', $fill);
            $pdf->Cell(15, 7, $qty, 'LR', 1, 'C', $fill);
            $fill = !$fill; // Alterna lo sfondo per la prossima riga
        }
        // Chiusura della tabella
        $pdf->Cell(185, 0, '', 'T');

        ob_end_clean();
        $pdf->Output('DISTINTA DI PRELIEVO.pdf', 'I');
        exit;
    } else {
        echo "<p>Errore nella query o nessun risultato corrispondente.</p>";
    }
}
?>

<form action="eti_decode.php" method="POST">
    <div class="form-group">
        <label for="azione">Azione:</label>
        <select class="form-control" id="azione" name="azione" required>
            <option value="PRELIEVO">PRELIEVO</option>
            <option value="VERSAMENTO">VERSAMENTO</option>
        </select>
    </div>
    <div class="form-group">
        <label for="barcodes">Inserisci i barcode:</label>
        <textarea class="form-control" id="barcodes" name="barcodes" required rows="10"></textarea>
    </div>
    <button class="btn btn-primary" type="submit" style="width:100%">GENERA</button>
</form>
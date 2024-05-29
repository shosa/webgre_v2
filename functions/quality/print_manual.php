<?php
session_start();
require_once '../../config/config.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/assets/tcpdf/tcpdf.php';
require_once BASE_PATH . '/assets/tcpdf/tcpdf_barcodes_1d.php';

try {
    $pdo = getDbInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch barcodes
    $sql = "SELECT code, test FROM cq_barcodes";
    $stmt = $pdo->query($sql);
    $barcodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Set table header
    $pdf->Cell(90, 10, 'BARCODE', 1, 0, 'C');
    $pdf->Cell(90, 10, 'TIPO TEST', 1, 1, 'C');
    $style = array(
        'position' => '',
        'align' => 'C',
        'stretch' => false,
        'fitwidth' => true,
        'cellfitalign' => '',
        'border' => false,
        'hpadding' => 'auto',
        'vpadding' => 'auto',
        'fgcolor' => array(0, 0, 0),
        'bgcolor' => false,
        'text' => true,
        'font' => 'helvetica',
        'fontsize' => 10,
        'stretchtext' => 3,
    );
    foreach ($barcodes as $barcode) {
        // Generate barcode directly within the cell
        $pdf->Cell(90, 10, $pdf->write1DBarcode($barcode['code'], 'C128', '', '', '', 18, 0.4, $style, 'N'), 1, 0, 'C');
        $pdf->Cell(90, 10, $barcode['test'], 1, 1, 'C');
    }

    // Close and output PDF
    $pdf->Output('barcode_report.pdf', 'D');

} catch (PDOException $e) {
    echo "Errore di connessione: " . $e->getMessage();
}
?>
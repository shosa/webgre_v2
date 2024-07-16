<?php
session_start();
require_once '../../config/config.php';
require_once '../../utils/log_utils.php';
require_once BASE_PATH . '/vendor/tcpdf/tcpdf.php';
require_once BASE_PATH . '/vendor/tcpdf/tcpdf_barcodes_1d.php';

// Funzione per inizializzare il database
function initializeDatabase()
{
    try {
        $pdo = getDbInstance();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Errore di connessione: " . $e->getMessage());
    }
}

// Funzione per ottenere i dati dei barcode
function fetchBarcodes($pdo)
{
    $sql = "SELECT code, test FROM cq_barcodes";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Funzione per configurare e aggiungere intestazioni e piè di pagina
class PDF extends TCPDF
{
    // Page header
    public function Header()
    {
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'Tabellone operativo Barcode CQ', 0, 1, 'C');
    }

    // Page footer
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

// Funzione per creare il PDF
function createPDF($barcodes)
{
    $pdf = new PDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Set table header
    $pdf->Ln();
    $pdf->Cell(100, 10, 'TIPO TEST', 1, 0, 'C');
    $pdf->Cell(90, 10, 'BARCODE', 1, 1, 'C');

    // Set barcode style
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
        // Add test type cell

        $pdf->Cell(100, 20, $barcode['test'], 1, 0, 'C');

        // Get the current X and Y position
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        // Move X position to the right to start the next cell
        $pdf->SetX($x);

        // Create a new cell for the barcode 
        $pdf->Cell(90, 20, '', 1, 1, 'C');

        // Write the barcode in the cell
        $pdf->write1DBarcode($barcode['code'], 'C128', $x + 20, $y + 3, 88, 16, 0.4, $style, 'N');
    }

    // Close and output PDF
    $pdf->Output('barcode_report.pdf', 'D');

}

// Main execution
$pdo = initializeDatabase();
$barcodes = fetchBarcodes($pdo);
createPDF($barcodes);
?>
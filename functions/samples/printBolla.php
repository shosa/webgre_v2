<?php
require_once '../../config/config.php';
require_once (BASE_PATH . '/assets/tcpdf/tcpdf.php');
require_once (BASE_PATH . '/assets/tcpdf/tcpdf_barcodes_1d.php');
require_once BASE_PATH . '/helpers/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';

// Controllo se l'ID del modello è passato come GET
if (!isset($_GET['model_id'])) {
    die("ID del modello non fornito.");
}

$modelId = $_GET['model_id'];

$pdo = getDbInstance();

// Recupera i dati del modello
$stmt = $pdo->prepare("SELECT * FROM samples_modelli WHERE id = :model_id");
$stmt->execute(['model_id' => $modelId]);
$model = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$model) {
    die("Modello non trovato.");
}

// Recupera i dati della DIBA
$stmt = $pdo->prepare("SELECT * FROM samples_diba WHERE modello_id = :model_id ORDER BY ID ASC");
$stmt->execute(['model_id' => $modelId]);
$dibaEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Creazione del documento PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Impostazioni generali del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('WEBGRE');
$pdf->SetTitle('WorkSheet');
$pdf->SetSubject('WorkSheet ' . htmlspecialchars($model['nome_modello']));
$pdf->SetKeywords('TCPDF, PDF, bolla, produzione, modello');

// Imposta margini
$pdf->SetMargins(10, 10, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Imposta il font
$pdf->SetFont('helvetica', '', 10);

// Aggiungi una pagina
$pdf->AddPage();

// **Aggiungi rettangolo nero e testo bianco**
$pdf->SetFillColor(0, 0, 0); // Nero
$pdf->SetTextColor(255, 255, 255); // Bianco
$pdf->Rect(10, 10, $pdf->GetPageWidth() - 20, 10, 'F'); // Rettangolo nero
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetXY(10, 10); // Posiziona il testo al centro del rettangolo
$pdf->Cell($pdf->GetPageWidth() - 20, 10, 'WORKSHEET CAMPIONI', 0, 1, 'C', false);
$pdf->Ln(10); // Spazio sotto il rettangolo

// Dettagli del Modello con immagine a sinistra
$pdf->SetTextColor(0); // Ripristina il colore del testo
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Dettagli:', 0, 1, 'L');
$pdf->SetLineWidth(0.3);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(10, $pdf->GetY(), $pdf->GetPageWidth() - 10, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 10);

$spacingY = 8; // Spaziatura verticale tra le righe di testo

// Layout della sezione immagine e dettagli
if (!empty($model['immagine']) && file_exists("../../functions/samples/img/" . $model['immagine'])) {
    $currentY = $pdf->GetY();
    $imagePath = BASE_PATH . "/functions/samples/img/" . $model['immagine'];
    $imageSize = 50; // Dimensione del quadrato
    $padding = 5; // Spazio tra l'immagine e il testo

    // Inserisci il bordo 


    // Inserisci l'immagine
    $pdf->Image($imagePath, 15, $currentY, $imageSize, $imageSize, '', '', '', false, 300);

    // Calcola la posizione di partenza per il testo
    $textStartX = 15 + $imageSize + $padding;
    $textStartY = $currentY;

    // Inserisci i dettagli del modello accanto all'immagine
    $pdf->SetXY($textStartX, $textStartY);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'MODELLO:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 15);
    $pdf->SetX($textStartX);
    $pdf->Cell(0, $spacingY, htmlspecialchars($model['nome_modello']), 0, 1, 'L');

    $pdf->SetXY($textStartX, $textStartY);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'FORMA:', 0, 1, 'R');
    $pdf->SetFont('helvetica', '', 15);
    $pdf->SetX($textStartX);
    $pdf->Cell(0, $spacingY, htmlspecialchars($model['forma']), 0, 1, 'R');

    $pdf->SetXY($textStartX, $textStartY + $spacingY * 2);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'VARIANTE:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 15);
    $pdf->SetX($textStartX);
    $pdf->MultiCell(0, $spacingY, htmlspecialchars($model['variante']), 0, 'L', 0, 1);

    $pdf->SetXY($textStartX, $textStartY + $spacingY * 2);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'DATA DI CONSEGNA:', 0, 1, 'R');
    $pdf->SetFont('helvetica', '', 15);
    $pdf->SetX($textStartX);
    $pdf->Cell(0, $spacingY, htmlspecialchars(date('d/m/Y', strtotime($model['consegna']))), 0, 1, 'R');

    $pdf->SetXY($textStartX, $textStartY + $spacingY * 4);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'NOTE:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetX($textStartX);
    // Utilizza MultiCell per le note per consentire il wrap del testo
    $pdf->MultiCell(85, $spacingY, htmlspecialchars($model['note']), 0, 'L');

    // Sposta il cursore sotto l'immagine per la sezione successiva
    $pdf->SetY($currentY + $imageSize);
} else {
    // Se non c'è immagine, inserisci solo i dettagli
    $currentY = $pdf->GetY();
    $padding = 5; // Spazio tra l'immagine e il testo
    $textStartX = 15 + $padding;
    $textStartY = $currentY;
    $pdf->SetXY($textStartX, $textStartY);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'MODELLO:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 15);
    $pdf->SetX($textStartX);
    $pdf->Cell(0, $spacingY, htmlspecialchars($model['nome_modello']), 0, 1, 'L');

    $pdf->SetXY($textStartX, $textStartY);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'FORMA:', 0, 1, 'R');
    $pdf->SetFont('helvetica', '', 15);
    $pdf->SetX($textStartX);
    $pdf->Cell(0, $spacingY, htmlspecialchars($model['forma']), 0, 1, 'R');

    $pdf->SetXY($textStartX, $textStartY + $spacingY * 2);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'VARIANTE:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 15);
    $pdf->SetX($textStartX);
    $pdf->MultiCell(0, $spacingY, htmlspecialchars($model['variante']), 0, 'L', 0, 1);

    $pdf->SetXY($textStartX, $textStartY + $spacingY * 2);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'DATA DI CONSEGNA:', 0, 1, 'R');
    $pdf->SetFont('helvetica', '', 15);
    $pdf->SetX($textStartX);
    $pdf->Cell(0, $spacingY, htmlspecialchars(date('d/m/Y', strtotime($model['consegna']))), 0, 1, 'R');

 
    $pdf->SetXY($textStartX, $textStartY + $spacingY * 4);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'NOTE:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetX($textStartX);
    // Utilizza MultiCell per le note per consentire il wrap del testo
    $pdf->MultiCell(130, $spacingY, htmlspecialchars($model['note']), 0, 'L');
}

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, 'WORKSHEET #:', 0, 1, 'R');
$pdf->SetFont('helvetica', '', 18);

$pdf->Cell(0, $spacingY, htmlspecialchars($modelId), 0, 1, 'R');
$pdf->Ln(1);

// Posizione del barcode
$barcodeX = $pdf->GetPageWidth() - 50; // Posizione X del barcode, considerando una larghezza di 50mm

// Aggiungi il barcode
$barcodeOptions = array('code' => 'C128', 'display' => true, 'scale' => 0.75, 'text' => $modelId, 'align' => 'R', 'label' => false);
$barcode = $pdf->write1DBarcode($modelId, 'C128', $barcodeX, '', '', 10, 0.5, $barcodeOptions);
$pdf->Ln(10);

// Dettagli della DIBA
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Materiali:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// Tabella DIBA
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.3);

$header = ['Tipo', 'Descrizione', 'Note', 'UM', 'Cons.'];
$w = [20, 80, 60, 15, 15];
$pdf->SetFont('helvetica', 'B', 10);

// Header della tabella
$pdf->SetFillColor(224, 235, 255);
$pdf->SetTextColor(0);
foreach ($header as $key => $col) {
    $pdf->Cell($w[$key], 7, $col, 1, 0, 'C', 1);
}
$pdf->Ln();

// Dati della tabella
$pdf->SetFont('helvetica', '', 8);
$pdf->SetDrawColor(200, 200, 200); // Colore grigio chiaro per il bordo
$pdf->SetLineWidth(0.2); // Spessore del bordo
$rowHeight = 8; // Altezza della riga

// Funzione per centrare verticalmente il testo
function centerVerticalText($pdf, $width, $height, $text)
{
    $pdf->SetFont('', '', 8);
    $pdf->MultiCell($width, 1, $text, 0, 'L', 0, 0, '', '', true, 0, false, true, $height, 'M');
}

foreach ($dibaEntries as $entry) {
    // Tipo
    $pdf->MultiCell($w[0], $rowHeight, htmlspecialchars($entry['posizione']), 1, 'L', 0, 0, '', '', true, 0, false, true, $rowHeight, 'M');

    // Descrizione con rientro
    $pdf->MultiCell($w[1], $rowHeight, '  ' . htmlspecialchars($entry['descrizione']), 1, 'L', 0, 0, '', '', true, 0, false, true, $rowHeight, 'M'); // Aggiungi due spazi per il rientro

    // Note
    $pdf->MultiCell($w[2], $rowHeight, htmlspecialchars($entry['note']), 1, 'L', 0, 0, '', '', true, 0, false, true, $rowHeight, 'M');

    // UM
    $pdf->MultiCell($w[3], $rowHeight, htmlspecialchars($entry['unita_misura']), 1, 'C', 0, 0, '', '', true, 0, false, true, $rowHeight, 'M');

    // Consumo
    $pdf->MultiCell($w[4], $rowHeight, htmlspecialchars($entry['consumo']), 1, 'C', 0, 1, '', '', true, 0, false, true, $rowHeight, 'M');
}
$pdf->Ln(10);

// Recupera la posizione attuale
$currentY = $pdf->GetY();

// Etichette e contenuti per i QR code
$labels = ['TAGLIO', 'ORLATURA', 'MONTAGGIO', 'SPEDITO'];
$qrWidth = 20; // Larghezza del QR code
$labelHeight = 6; // Altezza dell'etichetta

foreach ($labels as $index => $label) {
    $qrX = ($pdf->GetPageWidth() / 4) * $index + 10;
    $qrY = $currentY + $labelHeight + 5; // Posizione Y per il QR code

    // Genera il contenuto del codice QR
    $qrContent = $dominio . "/functions/samples/updateAvanzamento?action=" . $label . "&id=" . $modelId;

    // Aggiungi l'etichetta
    $pdf->SetXY($qrX, $currentY);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell($qrWidth, $labelHeight, $label, 0, 0, 'C');
    $pdf->SetLineWidth(0.1);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(10, $pdf->GetY(), $pdf->GetPageWidth() - 10, $pdf->GetY());
    // Aggiungi il codice QR
    $pdf->write2DBarcode($qrContent, 'QRCODE,L', $qrX, $qrY, $qrWidth, $qrWidth);
}

// Output del PDF
$pdf->Output('Worksheet_' . $modelId . '.pdf', 'D');
?>
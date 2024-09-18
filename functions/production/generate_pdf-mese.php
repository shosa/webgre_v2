<?php
// Include la libreria TCPDF
require ("../../config/config.php");
require_once BASE_PATH . '/vendor/autoload.php'; // Path to PhpSpreadsheet autoload file
// Ricevi i parametri month e day dalla richiesta GET
$month = $_GET['month'];
// Esegui una query per ottenere i dati dalla tabella mesi
// Sostituisci questi dettagli con le tue credenziali di connessione al database
try {
    // Crea una connessione al database utilizzando PDO
    $pdo = getDbInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Esegui una query per ottenere i dati dal database
    $sql = "SELECT ID, MESE, GIORNO, NOMEGIORNO, MANOVIA1, MANOVIA2, MANOVIA3, ORLATURA1, ORLATURA2, ORLATURA3,ORLATURA4, TAGLIO1, TAGLIO2, TOTALITAGLIO, TOTALIORLATURA, TOTALIMONTAGGIO
            FROM prod_mesi
            WHERE MESE = :month AND NOMEGIORNO <> 'DOMENICA'
            ORDER BY ID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['month' => $month]);
    // Crea un nuovo documento PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // Imposta il titolo del documento
    $pdf->SetTitle('PRODUZIONE MESE DI ' . $month);
    // Aggiungi una pagina vuota
    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->SetFillColor(255, 255, 255); // Sfondo bianco
    $pdf->SetTextColor(0, 0, 0); // Testo nero
    $pdf->AddPage();
    $pdf->Cell(0, 3, "RAPPORTO DI PRODUZIONE DEL MESE DI ", 0, 1, 'C', true);
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 3, $month . " 2024", 0, 1, 'C', true);
    // Imposta il colore di sfondo e il colore del testo per le intestazioni
    $pdf->SetFillColor(0, 0, 0); // Sfondo nero
    $pdf->SetTextColor(255, 255, 255); // Testo bianco
    $pdf->SetFont('helvetica', 'B', 10);
    // Aggiungi le intestazioni della tabella
    $pdf->Cell(28, 8, 'GIORNO', 1, 0, 'C', 1); // 'C' per centrare il testo
    $pdf->Cell(18, 8, 'TAGL1', 1, 0, 'C', 1);
    $pdf->Cell(18, 8, 'TAGL2', 1, 0, 'C', 1);
    $pdf->Cell(18, 8, 'ORL1', 1, 0, 'C', 1);
    $pdf->Cell(18, 8, 'ORL2', 1, 0, 'C', 1);
    $pdf->Cell(18, 8, 'ORL3', 1, 0, 'C', 1);
    $pdf->Cell(18, 8, 'ORL4', 1, 0, 'C', 1);
    $pdf->Cell(18, 8, 'MONT1', 1, 0, 'C', 1);
    $pdf->Cell(18, 8, 'MONT2', 1, 0, 'C', 1);
    $pdf->Cell(18, 8, 'MONT3', 1, 0, 'C', 1);
    $pdf->Ln(); // Vai alla riga successiva
    // Ripristina il colore di sfondo e il colore del testo predefiniti
    $pdf->SetFillColor(255, 255, 255); // Sfondo bianco
    $pdf->SetTextColor(0, 0, 0); // Testo nero
    $totTaglio1 = 0;
    $totTaglio2 = 0;
    $totTagliato = 0;
    $totOrlatura1 = 0;
    $totOrlatura2 = 0;
    $totOrlatura3 = 0;
    $totOrlatura4 = 0;
    $totOrlato = 0;
    $totManovia1 = 0;
    $totManovia2 = 0;
    $totManovia3 = 0;
    $totMontato = 0;
    $pdf->SetFillColor(0, 0, 0); // Sfondo nero per le intestazioni
    $pdf->SetTextColor(255, 255, 255); // Testo bianco per le intestazioni
    $alternateColor = false; // Variabile booleana per alternare i colori delle righe
    // Aggiungi i dati ottenuti dalla query al PDF
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Alterna il colore della riga
        if ($alternateColor) {
            $pdf->SetFillColor(240, 240, 240); // Grigio chiaro
            $pdf->SetTextColor(0, 0, 0); // Testo nero per il colore di sfondo grigio chiaro
        } else {
            $pdf->SetFillColor(255, 255, 255); // Bianco
            $pdf->SetTextColor(0, 0, 0); // Testo nero per il colore di sfondo bianco
        }
        $alternateColor = !$alternateColor; // Cambia il colore per la prossima riga
        $totTaglio1 += (int) $row['TAGLIO1'];
        $totTaglio2 += (int) $row['TAGLIO2'];
        $totTagliato = $totTaglio1 + $totTaglio2;
        $totOrlatura1 += (int) $row['ORLATURA1'];
        $totOrlatura2 += (int) $row['ORLATURA2'];
        $totOrlatura3 += (int) $row['ORLATURA3'];
        $totOrlatura4 += (int) $row['ORLATURA4'];
        $totOrlato = $totOrlatura1 + $totOrlatura2 + $totOrlatura3 + $totOrlatura4;
        $totManovia1 += (int) $row['MANOVIA1'];
        $totManovia2 += (int) $row['MANOVIA2'];
        $totManovia3 += (int) $row['MANOVIA3'];
        $totMontato = $totManovia1 + $totManovia2 + $totManovia3;
        // Modifica questa parte per adattarla ai tuoi dati
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(6, 8, $row['GIORNO'], 1, 0, 'C', 1);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(22, 8, $row['NOMEGIORNO'], 1, 0, 'C', 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(18, 8, $row['TAGLIO1'], 1, 0, 'C', 1);
        $pdf->Cell(18, 8, $row['TAGLIO2'], 1, 0, 'C', 1);
        $pdf->Cell(18, 8, $row['ORLATURA1'], 1, 0, 'C', 1);
        $pdf->Cell(18, 8, $row['ORLATURA2'], 1, 0, 'C', 1);
        $pdf->Cell(18, 8, $row['ORLATURA3'], 1, 0, 'C', 1);
        $pdf->Cell(18, 8, $row['ORLATURA4'], 1, 0, 'C', 1);
        $pdf->Cell(18, 8, $row['MANOVIA1'], 1, 0, 'C', 1);
        $pdf->Cell(18, 8, $row['MANOVIA2'], 1, 0, 'C', 1);
        $pdf->Cell(18, 8, $row['MANOVIA3'], 1, 0, 'C', 1);
        $pdf->Ln(); // Vai alla riga successiva
    }
    $pdf->SetFillColor(0, 0, 0); // Sfondo nero
    $pdf->SetTextColor(255, 255, 255); // Testo bianco
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(28, 8, 'TOTALI', 1, 0, 'C', 1); // 'C' per centrare il testo
    $pdf->SetFillColor(255, 255, 255); // Sfondo bianco
    $pdf->SetTextColor(0, 0, 0); // Testo nero
    $pdf->Cell(18, 8, $totTaglio1, 1, 0, 'C', 1);
    $pdf->Cell(18, 8, $totTaglio2, 1, 0, 'C', 1);
    $pdf->Cell(18, 8, $totOrlatura1, 1, 0, 'C', 1);
    $pdf->Cell(18, 8, $totOrlatura2, 1, 0, 'C', 1);
    $pdf->Cell(18, 8, $totOrlatura3, 1, 0, 'C', 1);
    $pdf->Cell(18, 8, $totOrlatura4, 1, 0, 'C', 1);
    $pdf->Cell(18, 8, $totManovia1, 1, 0, 'C', 1);
    $pdf->Cell(18, 8, $totManovia2, 1, 0, 'C', 1);
    $pdf->Cell(18, 8, $totManovia3, 1, 0, 'C', 1);
    $pdf->Ln(12); // Vai alla riga successiva
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(40, 8, "TOTALE TAGLIATO", 1, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetFillColor(255, 255, 255); // Sfondo bianco
    $pdf->Cell(23, 8, $totTagliato, 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(40, 8, "TOTALE ORLATO", 1, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(23, 8, $totOrlato, 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(40, 8, "TOTALE MONTATO", 1, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(24, 8, $totMontato, 1, 1, 'C');
    // Calcola la larghezza totale delle celle dei totali
    $widthTotal = 30 + 20 + 30 + 20 + 30 + 20;
    // Calcola la posizione X per centrare i totali nella pagina
    $pageWidth = $pdf->getPageWidth();
    $xCentered = ($pageWidth - $widthTotal) / 2;
    // Sposta la posizione corrente per centrare i totali
    $pdf->SetX($xCentered);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(170, 15, 'CALZATURIFICIO EMMEGIEMME SHOES SRL', 0, 0, "R"); // L'ultimo parametro "1" imposta lo sfondo
    // Genera il file PDF
    $pdf->Output('PRODUZIONE.pdf', 'I');
} catch (PDOException $e) {
    // Gestisci eventuali eccezioni
    echo 'Errore: ' . $e->getMessage();
}
?>
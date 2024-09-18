<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/log_utils.php';
require_once BASE_PATH . '/vendor/autoload.php'; // Path to PhpSpreadsheet autoload file
// Leggi i dati inviati dalla richiesta POST
$postdata = file_get_contents("php://input");
$request = json_decode($postdata, true);
// Verifica se sono stati ricevuti i dati dei cartellini
if (isset($request['cartellini'])) {
    $cartellini = $request['cartellini'];
    try {
        // Ottieni l'istanza del database
        $db = getDbInstance();
        // Costruisci il placeholder per i cartellini nella query SQL
        $placeholders = rtrim(str_repeat('?, ', count($cartellini)), ', ');
        // Query per ottenere i dati necessari per i cartellini specificati
        $query = "
            SELECT 
                tl.cartel, 
                tl.note,
                d.`Commessa Cli`,
                tt.name AS type_name, 
                tl.lot
            FROM track_links tl
            JOIN track_types tt ON tl.type_id = tt.id
            JOIN dati d ON d.cartel = tl.cartel
            WHERE tl.cartel IN ($placeholders)
            ORDER BY tl.cartel ASC, tt.name ASC, tl.lot ASC";
        // Prepara e esegui la query
        $stmt = $db->prepare($query);
        $stmt->execute($cartellini);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Raccogli i dati in una struttura raggruppata per cartellino e tipo
        $data = [];
        foreach ($results as $row) {
            $cartel = $row['cartel'];
            $type_name = $row['type_name'];
            if (!isset($data[$cartel])) {
                $data[$cartel] = [
                    'Commessa Cli' => $row['Commessa Cli'],
                    'types' => []
                ];
            }
            if (!isset($data[$cartel]['types'][$type_name])) {
                $data[$cartel]['types'][$type_name] = [];
            }
            $data[$cartel]['types'][$type_name][] = $row['lot'];
        }
        // Creazione del documento PDF utilizzando TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // Impostazioni del documento PDF
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Emmegiemme');
        $pdf->SetTitle('Fiches Cartellini');
        $pdf->SetSubject('Report');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        // Itera sui cartellini per costruire le pagine PDF
        foreach ($data as $cartel => $details) {
            $pdf->AddPage();
            // Titolo della pagina
            $pdf->SetFont('helvetica', '', 15);
            $pdf->Cell(0, 10, "DETTAGLIO LOTTI", 0, 1, 'C');
            $pdf->Ln(8);
            // Dettagli del cartellino
            $pdf->SetFont('helvetica', 'B', 18);
            $pdf->Cell(0, 10, $cartel . " / " . $details['Commessa Cli'], 0, 1, 'C');
            $pdf->Ln(8);
            // Itera sui tipi di lotto
            foreach ($details['types'] as $type_name => $lots) {
                // Stampa il nome del tipo di lotto con testo bianco su sfondo nero
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->SetTextColor(255, 255, 255); // Testo bianco
                $pdf->SetFillColor(0, 0, 0); // Sfondo nero
                $pdf->Cell(0, 10, $type_name, 0, 1, 'C', 1); // '1' abilita il riempimento
                $pdf->Ln(4);
                // Ripristina il colore del testo per i lotti
                $pdf->SetTextColor(0, 0, 0); // Testo nero
                // Stampa i lotti per il tipo di lotto corrente
                $pdf->SetFont('helvetica', '', 10);
                foreach ($lots as $lot) {
                    $pdf->Cell(0, 10, $lot, 0, 1, 'C');
                    $pdf->Ln(2);
                }
                $pdf->Ln(4); // Aggiungi spazio tra i tipi di lotti
            }
        }
        // Output del documento PDF
        $pdfContent = $pdf->Output('', 'S');
        // Invia il contenuto del PDF come risposta
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="report.pdf"');
        echo $pdfContent;
    } catch (Exception $e) {
        // Gestione degli errori
        http_response_code(500);
        echo json_encode(array('message' => 'Errore del server: ' . $e->getMessage()));
    }
} else {
    // Dati non ricevuti correttamente
    http_response_code(400);
    echo json_encode(array('message' => 'Dati non ricevuti correttamente.'));
}
?>
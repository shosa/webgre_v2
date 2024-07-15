<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/utils/log_utils.php';
require_once '../../assets/tcpdf/tcpdf.php';

// Leggi il payload JSON inviato dal client
$postdata = file_get_contents("php://input");
$request = json_decode($postdata, true);

// Verifica se cartellini è stato ricevuto correttamente
if (isset($request['cartellini'])) {
    $cartellini = $request['cartellini'];

    try {
        // Connessione al database
        $db = getDbInstance();

        // Preparazione della query per ottenere i dati
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

        // Raggruppamento dei risultati per 'Descrizione Articolo', 'cartel', 'Commessa Cli', 'type_name' e 'lot'
        $groupedResults = [];
        foreach ($results as $row) {
            $groupedResults[$row['Descrizione Articolo']][$row['Commessa Cli']][$row['cartel']][$row['type_name']][] = [
                'lot' => $row['lot'],
            ];
        }

        // Generazione del PDF usando TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Impostazioni del documento
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Packing List - Per Cartellino');
        $pdf->SetSubject('Report');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // Margini del documento
        $pdf->SetMargins(10, 10, 10); // margini (left, top, right)
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);

        // Aggiunta di una pagina
        $pdf->AddPage();

        // Impostazione dell'altezza delle celle
        $pdf->SetCellHeightRatio(0.5); // Aumenta l'altezza delle celle per migliorare la leggibilità

        // Contenuto del documento   // Contenuto del documento
        $pdf->SetFont('helvetica', '', 15);
        $pdf->SetFillColor(204, 228, 255); // Colore di sfondo grigio chiaro
        $pdf->Cell(0, 10, "PACKING LIST - Dettaglio lotti di produzione per Cartellini", 0, 1, 'L', true);

        $pdf->SetFillColor(204, 228, 255); // Colore di sfondo grigio chiaro


        // Itera attraverso i gruppi di risultati
        foreach ($groupedResults as $descrizioneArticolo => $commesse) {
            $pdf->SetFont('helvetica', 'B', 12);
            // Mostra la Descrizione Articolo
            $pdf->Cell(0, 10, $descrizioneArticolo, 0, 1, 'L');
            $pdf->Ln(1); // Spazio dopo il titolo

            // Itera attraverso le commesse
            foreach ($commesse as $commessa => $cartellini) {
                $pdf->SetFont('helvetica', '', 10);
                // Itera attraverso i cartellini della commessa
                foreach ($cartellini as $cartel => $types) {
                    // Intestazione del cartellino e commessa
                    $pdf->SetFillColor(240, 240, 240); // Colore di sfondo grigio chiaro
                    $pdf->Cell(0, 10, "Cartellino: $cartel / Commessa: $commessa", 0, 1, 'L', true);

                    // Mostra i nomi dei tipi di articoli
                    $colWidth = 65; // Larghezza delle colonne
                    $pdf->SetFont('helvetica', 'B', 8);
                    foreach ($types as $type_name => $lots) {
                        $pdf->Cell($colWidth, 10, $type_name, 0, 0, 'C', false);
                    }
                    $pdf->Ln(); // Vai alla riga successiva

                    // Mostra i lotti sotto i tipi di articoli
                    $pdf->SetFont('helvetica', '', 8);
                    $maxRows = 0; // Numero massimo di righe tra i lotti per allineare le colonne
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
                        $pdf->Ln(); // Vai alla riga successiva
                    }
                    
                    $pdf->Ln(2); // Spazio tra diversi cartellini della stessa commessa
                }
                $pdf->Ln(4); // Spazio tra diverse commesse
            }
            $pdf->Ln(6); // Spazio tra diverse Descrizioni Articolo
        }

        // Salva il PDF come stringa e invialo come risposta
        $pdfContent = $pdf->Output('', 'S');
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="report.pdf"');
        echo $pdfContent;
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array('message' => 'Errore del server: ' . $e->getMessage()));
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array('message' => 'Dati non ricevuti correttamente.'));
}
?>

<?php
// export_pdf.php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Verifica se è installata la libreria TCPDF
if (!class_exists('TCPDF')) {
    require_once BASE_PATH . '/vendor/autoload.php'; // Tenta di caricare automaticamente
    
    if (!class_exists('TCPDF')) {
        $_SESSION['error'] = "Libreria TCPDF non disponibile. Installa TCPDF per esportare in PDF.";
        header("Location: lista_macchinari");
        exit;
    }
}

try {
    // Ottieni l'istanza del database
    $pdo = getDbInstance();

    // Recupera i filtri di ricerca
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $tipologia = isset($_GET['tipologia']) ? $_GET['tipologia'] : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'data_creazione';
    $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

    // Query di base
    $query = "SELECT id, matricola, tipologia, fornitore, modello, data_acquisto, rif_fattura, note FROM mac_anag WHERE 1=1";
    $params = [];

    // Aggiunta dei filtri
    if (!empty($search)) {
        $query .= " AND (matricola LIKE ? OR fornitore LIKE ? OR modello LIKE ? OR note LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }

    if (!empty($tipologia)) {
        $query .= " AND tipologia = ?";
        $params[] = $tipologia;
    }

    // Ordinamento
    $validSortColumns = ['matricola', 'tipologia', 'fornitore', 'modello', 'data_acquisto', 'data_creazione'];
    $validSortOrders = ['ASC', 'DESC'];

    if (!in_array($sort, $validSortColumns)) {
        $sort = 'data_creazione';
    }

    if (!in_array($order, $validSortOrders)) {
        $order = 'DESC';
    }

    $query .= " ORDER BY $sort $order";

    // Esecuzione query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $macchinari = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Crea una classe PDF personalizzata
    class MYPDF extends TCPDF {
        // Intestazione
        public function Header() {
            // Logo
            //$image_file = K_PATH_IMAGES.'logo.jpg';
            //$this->Image($image_file, 15, 10, 30, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            
            // Titolo
            $this->SetFont('helvetica', 'B', 16);
            $this->Cell(0, 10, 'ELENCO MACCHINARI AZIENDALI', 0, 1, 'C', 0);
            
            $this->SetFont('helvetica', 'I', 9);
            $this->Cell(0, 5, 'Generato il: ' . date('d/m/Y H:i'), 0, 1, 'R', 0);
            
            // Filtri applicati
            $filtriText = 'Filtri: ';
            if (!empty($_GET['search'])) {
                $filtriText .= "Ricerca \"" . $_GET['search'] . "\" | ";
            }
            if (!empty($_GET['tipologia'])) {
                $filtriText .= "Tipologia \"" . $_GET['tipologia'] . "\" | ";
            }
            $sort = $_GET['sort'] ?? 'data_creazione';
            $order = $_GET['order'] ?? 'DESC';
            $filtriText .= "Ordinamento per \"$sort\" " . strtolower($order);
            
            $this->Cell(0, 5, $filtriText, 0, 1, 'L', 0);
            
            $this->Ln(5);
        }

        // Piè di pagina
        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0);
        }
    }

    // Crea il documento PDF
    $pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);

    // Imposta informazioni del documento
    $pdf->SetCreator('Sistema Gestione Macchinari');
    $pdf->SetAuthor('Azienda');
    $pdf->SetTitle('Elenco Macchinari Aziendali');
    $pdf->SetSubject('Elenco Macchinari');
    $pdf->SetKeywords('macchinari, elenco, azienda');

    // Imposta margini
    $pdf->SetMargins(10, 30, 10);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);

    // Imposta auto page break
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Set font
    $pdf->SetFont('helvetica', '', 9);

    // Aggiungi una pagina
    $pdf->AddPage();

    // Intestazioni tabella
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(78, 115, 223); // Blu primario
    $pdf->SetTextColor(255, 255, 255); // Bianco
    
    // Larghezza colonne (totale 265mm disponibili in landscape)
    $widths = [10, 40, 35, 35, 35, 25, 35, 40];
    
    // Intestazioni
    $headers = ['ID', 'Matricola', 'Tipologia', 'fornitore', 'Modello', 'Data Acquisto', 'Riferimento Fattura', 'Note'];
    foreach ($headers as $i => $header) {
        $pdf->Cell($widths[$i], 10, $header, 1, 0, 'C', 1);
    }
    $pdf->Ln();
    
    // Dati dei macchinari
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0, 0, 0); // Nero
    
    // Alterna il colore di sfondo per righe
    $fill = false;
    
    foreach ($macchinari as $macchinario) {
        // Sfondo alternato per le righe
        $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
        
        // ID
        $pdf->Cell($widths[0], 6, $macchinario['id'], 1, 0, 'C', $fill);
        
        // Matricola
        $pdf->Cell($widths[1], 6, $macchinario['matricola'], 1, 0, 'L', $fill);
        
        // Tipologia
        $pdf->Cell($widths[2], 6, $macchinario['tipologia'], 1, 0, 'L', $fill);
        
        // fornitore
        $pdf->Cell($widths[3], 6, $macchinario['fornitore'], 1, 0, 'L', $fill);
        
        // Modello
        $pdf->Cell($widths[4], 6, $macchinario['modello'], 1, 0, 'L', $fill);
        
        // Data acquisto
        $pdf->Cell($widths[5], 6, date('d/m/Y', strtotime($macchinario['data_acquisto'])), 1, 0, 'C', $fill);
        
        // Riferimento Fattura
        $pdf->Cell($widths[6], 6, $macchinario['rif_fattura'], 1, 0, 'L', $fill);
        
        // Note (limitare il testo a 40 caratteri circa)
        $note = $macchinario['note'];
        if (strlen($note) > 40) {
            $note = substr($note, 0, 37) . '...';
        }
        $pdf->Cell($widths[7], 6, $note, 1, 0, 'L', $fill);
        
        $pdf->Ln();
        
        // Alterna il colore di sfondo
        $fill = !$fill;
    }
    
    // Conteggio totale
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(array_sum($widths), 10, "Totale macchinari: " . count($macchinari), 0, 0, 'R');
    
    // Nome file
    $fileName = 'macchinari_' . date('Ymd_His') . '.pdf';
    
    // Chiudi e invia il PDF al browser
    $pdf->Output($fileName, 'D');
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = "Errore nell'esportazione PDF: " . $e->getMessage();
    header("Location: lista_macchinari");
    exit;
}
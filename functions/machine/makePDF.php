<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';
// Importa la libreria mPDF (assicurati che sia disponibile via Composer)
require_once BASE_PATH . '/vendor/autoload.php';

// Verifica che l'ID sia stato passato
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID macchinario non specificato.";
    header("Location: lista_macchinari");
    exit;
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = "ID macchinario non valido.";
    header("Location: lista_macchinari");
    exit;
}

// Ottieni l'istanza del database
$pdo = getDbInstance();

// Recupera i dati del macchinario
try {
    $stmt = $pdo->prepare("SELECT * FROM mac_anag WHERE id = ?");
    $stmt->execute([$id]);
    $macchinario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$macchinario) {
        $_SESSION['error'] = "Macchinario non trovato.";
        header("Location: lista_macchinari");
        exit;
    }
    
    // Calcolo età in anni
    $dataAcquisto = new DateTime($macchinario['data_acquisto']);
    $oggi = new DateTime();
    $eta = $dataAcquisto->diff($oggi)->y;
    
    // Recupera la cronologia delle manutenzioni
    $hasManutenzioni = false;
    try {
        $stmtManutenzioni = $pdo->prepare("SELECT * FROM mac_manutenzioni WHERE mac_id = ? ORDER BY data_manutenzione DESC");
        $stmtManutenzioni->execute([$id]);
        $manutenzioni = $stmtManutenzioni->fetchAll(PDO::FETCH_ASSOC);
        $hasManutenzioni = $stmtManutenzioni->rowCount() > 0;
    } catch (PDOException $e) {
        // La tabella manutenzioni potrebbe non esistere ancora
        $manutenzioni = [];
    }
    
    // Recupera gli allegati dalla tabella mac_anag_allegati
    $hasAllegati = false;
    try {
        $stmtAllegati = $pdo->prepare("SELECT * FROM mac_anag_allegati WHERE mac_id = ? ORDER BY data_caricamento DESC");
        $stmtAllegati->execute([$id]);
        $allegati = $stmtAllegati->fetchAll(PDO::FETCH_ASSOC);
        $hasAllegati = $stmtAllegati->rowCount() > 0;
    } catch (PDOException $e) {
        // La tabella potrebbe non esistere ancora
        $allegati = [];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Errore nel recupero dei dati: " . $e->getMessage();
    header("Location: lista_macchinari");
    exit;
}

// Helper per formattare la dimensione del file
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Inizializza mPDF
$mpdf = new \Mpdf\Mpdf([
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 15,
    'margin_bottom' => 15,
    'margin_header' => 10,
    'margin_footer' => 10
]);

// Aggiungi header e footer
$mpdf->SetHTMLHeader('
<div style="text-align: center; border-bottom: 1px solid #4e73df; padding-bottom: 5px; font-weight: bold; color: #4e73df;">
    <div style="float: left; width: 20%;">
        <img src="../../img/logo.png" width="200" alt="Logo" />
    </div>
    <div style="float: left; width: 60%; text-align: center; padding-top: 10px;">
        SCHEDA TECNICA MACCHINARIO
    </div>
    <div style="float: right; width: 20%; text-align: right; padding-top: 10px;">
        ' . date('d/m/Y') . '
    </div>
    <div style="clear: both;"></div>
</div>');

$mpdf->SetHTMLFooter('
<div style="text-align: center; border-top: 1px solid #ccc; padding-top: 5px; font-size: 9pt; color: #666;">
    <div style="float: left; width: 33%;">
        Scheda Macchinario #' . $id . '
    </div>
    <div style="float: left; width: 33%; text-align: center;">
        Pagina {PAGENO} di {nbpg}
    </div>
    <div style="float: right; width: 33%; text-align: right;">
        ' . date('d/m/Y H:i') . '
    </div>
    <div style="clear: both;"></div>
</div>');

// Crea il contenuto HTML del PDF
$html = '
<style>
    body { font-family: Arial, sans-serif; color: #333; }
    h1 { color: #4e73df; font-size: 18pt; margin-bottom: 10px; }
    h2 { color: #4e73df; font-size: 14pt; margin-top: 20px; margin-bottom: 5px; border-bottom: 1px solid #e3e6f0; padding-bottom: 5px; }
    h3 { color: #5a5c69; font-size: 12pt; margin-top: 15px; margin-bottom: 5px; }
    .details-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    .details-table tr { border-bottom: 1px solid #e3e6f0; }
    .details-table td { padding: 8px; vertical-align: top; }
    .details-table td:first-child { width: 40%; font-weight: bold; color: #4e73df; }
    .details-table td:last-child { width: 60%; }
    .notes { background-color: #f8f9fc; padding: 10px; border-radius: 4px; margin: 10px 0; }
    .section { margin-top: 30px; }
    .maintenance-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    .maintenance-table th { text-align: left; padding: 8px; background-color: #4e73df; color: white; font-weight: bold; }
    .maintenance-table tr { border-bottom: 1px solid #e3e6f0; }
    .maintenance-table td { padding: 8px; }
    .attachments-list { padding-left: 0; list-style-type: none; }
    .attachments-list li { padding: 6px 0; border-bottom: 1px solid #e3e6f0; }
    .status-badge { background-color: #1cc88a; color: white; padding: 4px 8px; border-radius: 4px; font-size: 10pt; }
</style>

<div class="header-info" style="padding-bottom: 20px; padding-top:20px; text-align: center;">
    <h1>' . htmlspecialchars($macchinario['marca'] ? $macchinario['marca'] . ' ' . $macchinario['modello'] : $macchinario['modello']) . '</h1>
    <div>
        <span class="status-badge">Matricola: ' . htmlspecialchars($macchinario['matricola']) . '</span>
    </div>
</div>

<h2>Informazioni Generali</h2>
<table class="details-table">
    <tr>
        <td>Tipologia:</td>
        <td>' . htmlspecialchars($macchinario['tipologia']) . '</td>
    </tr>
    <tr>
        <td>Marca:</td>
        <td>' . htmlspecialchars($macchinario['marca'] ?: 'Non specificata') . '</td>
    </tr>
    <tr>
        <td>Modello:</td>
        <td>' . htmlspecialchars($macchinario['modello']) . '</td>
    </tr>
    <tr>
        <td>Anno di Costruzione:</td>
        <td>' . htmlspecialchars($macchinario['anno_costruzione'] ?: 'Non specificato') . '</td>
    </tr>
    <tr>
        <td>Matricola/Numero di Serie:</td>
        <td>' . htmlspecialchars($macchinario['matricola']) . '</td>
    </tr>
</table>

<h2>Dettagli Acquisto</h2>
<table class="details-table">
    <tr>
        <td>Data di Acquisto:</td>
        <td>' . date('d/m/Y', strtotime($macchinario['data_acquisto'])) . ' (Età: ' . $eta . ' anni)</td>
    </tr>
    <tr>
        <td>Fornitore:</td>
        <td>' . htmlspecialchars($macchinario['fornitore']) . '</td>
    </tr>
    <tr>
        <td>Riferimento Fattura:</td>
        <td>' . htmlspecialchars($macchinario['rif_fattura'] ?: 'Non specificato') . '</td>
    </tr>
</table>

<h2>Documentazione</h2>
<table class="details-table">
    <tr>
        <td>Identificativo Locazione:</td>
        <td>' . htmlspecialchars($macchinario['locazione_documenti'] ?: 'Non specificata') . '</td>
    </tr>
</table>';

// Aggiungi la sezione note se presente
if (!empty($macchinario['note'])) {
    $html .= '
<h2>Note</h2>
<div class="notes">
    ' . nl2br(htmlspecialchars($macchinario['note'])) . '
</div>';
}

// Aggiungi la sezione allegati se presenti
if ($hasAllegati) {
    $html .= '
<h2>Allegati</h2>
<ul class="attachments-list">';
    
    foreach ($allegati as $allegato) {
        $html .= '
    <li>
        <strong>' . htmlspecialchars($allegato['nome_file']) . '</strong> 
        <span style="color: #4e73df; font-size: 9pt; text-transform: uppercase;">(' . htmlspecialchars(ucfirst($allegato['categoria'])) . ')</span>';
        
        if (!empty($allegato['descrizione'])) {
            $html .= '<br><i style="color: #5a5c69; font-size: 9pt;">' . htmlspecialchars($allegato['descrizione']) . '</i>';
        }
        
        $html .= '<br><span style="color: #858796; font-size: 8pt;">Caricato il ' . date('d/m/Y', strtotime($allegato['data_caricamento'])) . ' - ' . formatFileSize($allegato['dimensione']) . '</span>
    </li>';
    }
    
    $html .= '
</ul>';
}

// Aggiungi la sezione manutenzioni se presenti
if ($hasManutenzioni) {
    $html .= '
<h2>Storico Manutenzioni</h2>';
    
    if (count($manutenzioni) > 0) {
        $html .= '
<table class="maintenance-table">
    <thead>
        <tr>
            <th>Data</th>
            <th>Tipo</th>
            <th>Operatore</th>
            <th>Descrizione</th>
            <th>Stato</th>
        </tr>
    </thead>
    <tbody>';
        
        foreach ($manutenzioni as $manutenzione) {
            // Stile per lo stato
            switch ($manutenzione['stato']) {
                case 'completata':
                    $statoStyle = 'background-color: #1cc88a; color: white;';
                    break;
                case 'in_corso':
                    $statoStyle = 'background-color: #36b9cc; color: white;';
                    break;
                case 'richiesta':
                    $statoStyle = 'background-color: #f6c23e; color: white;';
                    break;
                case 'approvata':
                    $statoStyle = 'background-color: #4e73df; color: white;';
                    break;
                case 'rifiutata':
                    $statoStyle = 'background-color: #e74a3b; color: white;';
                    break;
                default:
                    $statoStyle = 'background-color: #858796; color: white;';
            }
            
            $html .= '
        <tr>
            <td>' . date('d/m/Y', strtotime($manutenzione['data_manutenzione'])) . '</td>
            <td>' . htmlspecialchars($manutenzione['tipo_id']) . '</td>
            <td>' . htmlspecialchars($manutenzione['operatore']) . '</td>
            <td>' . htmlspecialchars($manutenzione['descrizione']) . '</td>
            <td><span style="padding: 2px 6px; border-radius: 3px; font-size: 8pt; ' . $statoStyle . '">' . htmlspecialchars(ucfirst($manutenzione['stato'])) . '</span></td>
        </tr>';
        }
        
        $html .= '
    </tbody>
</table>';
    } else {
        $html .= '
<p style="color: #858796; font-style: italic;">Nessuna manutenzione registrata per questo macchinario.</p>';
    }
}

// Aggiungi le informazioni di sistema
$html .= '
<div class="section" style="margin-top: 30px; font-size: 8pt; color: #858796;">
    <h2>Informazioni di Sistema</h2>
    <table class="details-table">
        <tr>
            <td>ID Sistema:</td>
            <td>#' . $id . '</td>
        </tr>
        <tr>
            <td>Data Inserimento:</td>
            <td>' . date('d/m/Y H:i', strtotime($macchinario['data_creazione'])) . '</td>
        </tr>
        <tr>
            <td>Ultimo Aggiornamento:</td>
            <td>' . date('d/m/Y H:i', strtotime($macchinario['data_aggiornamento'])) . '</td>
        </tr>
        <tr>
            <td>Scheda Generata:</td>
            <td>' . date('d/m/Y H:i') . ' da ' . ($_SESSION['user_name'] ?? 'Utente') . '</td>
        </tr>
    </table>
</div>';

// Scrivi l'HTML nel PDF
$mpdf->WriteHTML($html);

// Output del PDF
$fileName = 'Scheda_Macchinario_' . preg_replace('/[^a-zA-Z0-9]/', '_', $macchinario['matricola']) . '_' . date('Ymd') . '.pdf';
$mpdf->Output($fileName, 'D');
exit;
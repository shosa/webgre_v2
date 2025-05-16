<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/vendor/autoload.php';

// Verifica presenza del parametro id_documento
$id_documento = $_GET['id_documento'] ?? null;

if (!$id_documento) {
    die('Parametro id_documento mancante');
}

// Connessione al database
$db = getDbInstance();

try {
    // Recupera dati del documento e del terzista
    $sql = "SELECT d.id, d.id_terzista, t.ragione_sociale 
            FROM exp_documenti d 
            JOIN exp_terzisti t ON d.id_terzista = t.id 
            WHERE d.id = :id_documento";

    $stmt = $db->prepare($sql);
    $stmt->execute([':id_documento' => $id_documento]);
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$documento) {
        die('Documento non trovato');
    }

    // Recupera dati dei colli
    $sql = "SELECT aspetto_colli, n_colli 
            FROM exp_piede_documenti 
            WHERE id_documento = :id_documento";

    $stmt = $db->prepare($sql);
    $stmt->execute([':id_documento' => $id_documento]);
    $piede = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$piede || !$piede['n_colli']) {
        die('Informazioni sui colli non trovate');
    }

    // Inizializza mPDF
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'L',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10
    ]);

    // Generazione PDF per ogni collo
    $n_colli = (int) $piede['n_colli'];
    $aspetto_colli = $piede['aspetto_colli'];
    $ragione_sociale = $documento['ragione_sociale'];

    // Salva come file singolo con tutti i segnacolli
    $mpdf->SetTitle('Segnacolli - ' . $ragione_sociale);

    for ($i = 1; $i <= $n_colli; $i++) {
        // Contenuto del segnacollo
        $html = <<<EOD
        <div style="text-align: center; padding: 20px;">
            <img src="img/small_logo.png" style="max-width: 400px; margin-bottom: 70px;">
            
            <div style="font-size: 40pt; font-weight: bold; margin: 20px 0;">X</div>
            
            <div style="font-size: 60pt; font-weight: bold; margin: 20px 0;">
                {$ragione_sociale}
            </div>
            
            <div style="text-align: right; font-size: 20pt; margin-top: 180px;">
                {$aspetto_colli} {$i} di {$n_colli}
            </div>
        </div>
EOD;

        $mpdf->AddPage();
        $mpdf->WriteHTML($html);
    }

    // Output del PDF
    $filename = 'Segnacolli_' . $id_documento . '_' . date('Ymd') . '.pdf';
    $mpdf->Output($filename, 'I'); // 'I' per visualizzare nel browser

} catch (Exception $e) {
    die('Errore: ' . $e->getMessage());
}
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
        'margin_bottom' => 10,
        'default_font' => 'dejavusans' // fallback perché CDN non è supportato
    ]);

    $n_colli = (int) $piede['n_colli'];
    $aspetto_colli = $piede['aspetto_colli'];
    $ragione_sociale = $documento['ragione_sociale'];
    $id_documento = $documento['id'];

    $mpdf->SetTitle('Segnacolli - ' . $ragione_sociale);

    for ($i = 1; $i <= $n_colli; $i++) {
        $html = <<<EOD
        <html>
        <head>
            <!-- Roboto CDN inclusion -->
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
            <style>
                body {
                    font-family: 'Roboto', sans-serif;
                    text-align: center;
                    padding: 20px;
                }
                .main-title {
                    font-size: 40pt;
                    font-weight: bold;
                    margin: 20px 0;
                }
                .company {
                    font-size: 60pt;
                    font-weight: bold;
                    margin: 20px 0;
                    color: white;
                    background-color: black;
                    padding: 10px;
                }
                .footer {
                    text-align: right;
                    font-size: 25pt;
                    margin-top: 160px;
                }
            </style>
        </head>
        <body>
            <img src="img/small_logo.png" style="width: 320px; margin-bottom: 20px;">
            <div class="main-title">X</div>
            <div class="company">{$ragione_sociale}</div>
            <div class="footer">
                <b>DDT {$id_documento}</b> | 
                {$aspetto_colli} {$i} di {$n_colli}
            </div>
        </body>
        </html>
        EOD;

        $mpdf->AddPage();
        $mpdf->WriteHTML($html);
    }

    $filename = 'Segnacolli' . $id_documento . '_' . date('Ymd') . '.pdf';
    $mpdf->Output($filename, 'I');

} catch (Exception $e) {
    die('Errore: ' . $e->getMessage());
}
?>

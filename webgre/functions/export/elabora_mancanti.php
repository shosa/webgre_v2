<?php
require_once '../../config/config.php';

$progressivo = $_POST['progressivo'];
$db = getDbInstance();

// Cancella i record dalla tabella exp_dati_mancanti
$db->where('id_documento', $progressivo);
$db->delete('exp_dati_mancanti');

// Recupera gli articoli con qta_reale minore di qta_originale
$articoliMancanti = $db->where('id_documento', $progressivo)
    ->where('qta_reale < qta_originale')
    ->get('exp_dati_articoli');

foreach ($articoliMancanti as $articolo) {
    // Arrotonda la quantità mancante a due cifre decimali
    $qta_mancante = round($articolo['qta_originale'] - $articolo['qta_reale'], 2);

    $data = [
        'id_documento' => $progressivo,
        'codice_articolo' => $articolo['codice_articolo'],
        'qta_mancante' => $qta_mancante
    ];
    $db->insert('exp_dati_mancanti', $data);
}

$response = [
    'success' => true,
    'message' => 'Operazione completata con successo!'
];

echo json_encode($response);
?>

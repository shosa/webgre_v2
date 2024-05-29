<?php
require_once '../../config/config.php';

$db = getDbInstance();
$data = json_decode(file_get_contents("php://input"), true);

$progressivo = $data['progressivo'];
$aspettoMerce = $data['aspettoMerce'];
$numeroColli = $data['numeroColli'];
$pesoLordo = $data['pesoLordo'];
$pesoNetto = $data['pesoNetto'];
$vociDoganali = $data['vociDoganali'];
$trasportatore = $data['trasportatore'];

// Verifica se esiste già un record per il progressivo
$db->where('id_documento', $progressivo);
$existingData = $db->getOne('exp_piede_documenti');

if ($existingData) {
    // Aggiorna il record esistente
    $dataToUpdate = [
        'aspetto_colli' => $aspettoMerce,
        'n_colli' => $numeroColli,
        'tot_peso_lordo' => $pesoLordo,
        'tot_peso_netto' => $pesoNetto,
        'trasportatore' => $trasportatore
    ];

    for ($i = 0; $i < count($vociDoganali); $i++) {
        $dataToUpdate['voce_' . ($i + 1)] = $vociDoganali[$i]['voce'];
        $dataToUpdate['peso_' . ($i + 1)] = $vociDoganali[$i]['peso'];
    }

    $db->where('id', $existingData['id']);
    $result = $db->update('exp_piede_documenti', $dataToUpdate);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Aggiornamento riuscito']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aggiornamento fallito']);
    }
} else {
    // Inserisci un nuovo record
    $dataToInsert = [
        'id_documento' => $progressivo,
        'aspetto_colli' => $aspettoMerce,
        'n_colli' => $numeroColli,
        'tot_peso_lordo' => $pesoLordo,
        'tot_peso_netto' => $pesoNetto,
        'trasportatore' => $trasportatore
    ];

    for ($i = 0; $i < count($vociDoganali); $i++) {
        $dataToInsert['voce_' . ($i + 1)] = $vociDoganali[$i]['voce'];
        $dataToInsert['peso_' . ($i + 1)] = $vociDoganali[$i]['peso'];
    }

    $result = $db->insert('exp_piede_documenti', $dataToInsert);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Inserimento riuscito']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Inserimento fallito']);
    }
}
?>
<?php
require_once '../../config/config.php';

$db = getDbInstance(); // deve restituire un'istanza PDO
$data = json_decode(file_get_contents("php://input"), true);

$progressivo = $data['progressivo'];
$aspettoMerce = $data['aspettoMerce'];
$numeroColli = $data['numeroColli'];
$pesoLordo = $data['pesoLordo'];
$pesoNetto = $data['pesoNetto'];
$vociDoganali = $data['vociDoganali'];
$trasportatore = $data['trasportatore'];
$consegnato = $data['consegnato'];


// Verifica se esiste già un record per il progressivo
$sqlCheck = "SELECT * FROM exp_piede_documenti WHERE id_documento = :progressivo LIMIT 1";
$stmt = $db->prepare($sqlCheck);
$stmt->execute(['progressivo' => $progressivo]);
$existingData = $stmt->fetch();

if ($existingData) {
    // Aggiorna il record esistente
    $dataToUpdate = [
        'aspetto_colli' => $aspettoMerce,
        'n_colli' => $numeroColli,
        'tot_peso_lordo' => $pesoLordo,
        'tot_peso_netto' => $pesoNetto,
        'trasportatore' => $trasportatore,
        'consegnato_per' => $consegnato
    ];

    for ($i = 0; $i < count($vociDoganali); $i++) {
        $dataToUpdate['voce_' . ($i + 1)] = $vociDoganali[$i]['voce'];
        $dataToUpdate['peso_' . ($i + 1)] = $vociDoganali[$i]['peso'];
    }

    // Costruisci dinamicamente la query
    $fields = [];
    foreach ($dataToUpdate as $key => $value) {
        $fields[] = "$key = :$key";
    }
    $dataToUpdate['id'] = $existingData['id']; // per la clausola WHERE

    $sqlUpdate = "UPDATE exp_piede_documenti SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $db->prepare($sqlUpdate);
    $result = $stmt->execute($dataToUpdate);

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
        'trasportatore' => $trasportatore,
        'consegnato_per' => $consegnato
    ];

    for ($i = 0; $i < count($vociDoganali); $i++) {
        $dataToInsert['voce_' . ($i + 1)] = $vociDoganali[$i]['voce'];
        $dataToInsert['peso_' . ($i + 1)] = $vociDoganali[$i]['peso'];
    }

    $columns = implode(', ', array_keys($dataToInsert));
    $placeholders = ':' . implode(', :', array_keys($dataToInsert));

    $sqlInsert = "INSERT INTO exp_piede_documenti ($columns) VALUES ($placeholders)";
    $stmt = $db->prepare($sqlInsert);
    $result = $stmt->execute($dataToInsert);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Inserimento riuscito']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Inserimento fallito']);
    }
}
?>

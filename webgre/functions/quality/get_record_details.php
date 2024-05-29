<?php
require_once '../../config/config.php';

if (isset($_GET['testid'])) {
    $testid = $_GET['testid'];

    $db = getDbInstance();
    $db->where('testid', $testid);
    $record = $db->getOne('cq_records');

    if ($record) {
        // Recupera il campo note dalla tabella cq_records
        $note = $record['note'];

        // Recupera il nome dell'operatore dalla tabella utenti
        $db->where('user_name', $record['operatore']);
        $user = $db->getOne('utenti', 'Nome');

        if ($user) {
            $operatore = $user['Nome'];
        } else {
            $operatore = 'Operatore non trovato';
        }

        $response = [
            'test' => $record['test'],
            'note' => $note,
            'operatore' => $operatore,
            'articolo' => $record['articolo'],
            'cartellino' => $record['cartellino'],
            'commessa' => $record['commessa'],
            'calzata' => $record['calzata']
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Record not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>

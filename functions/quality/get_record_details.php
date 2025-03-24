<?php
require_once '../../config/config.php';
if (isset($_GET['testid'])) {
    $testid = $_GET['testid'];
    try {
        // Connessione al database utilizzando PDO
        $db = getDbInstance();
        // Preparazione e esecuzione della query per cq_records
        $sqlRecord = "SELECT * FROM cq_records WHERE testid = :testid";
        $stmtRecord = $db->prepare($sqlRecord);
        $stmtRecord->bindParam(':testid', $testid, PDO::PARAM_INT);
        $stmtRecord->execute();
        $record = $stmtRecord->fetch(PDO::FETCH_ASSOC);
        if ($record) {
            // Recupera il campo note dalla tabella cq_records
            $note = $record['note'];
            // Preparazione e esecuzione della query per utenti
           
            $response = [
                'test' => $record['test'],
                'note' => $note,
                'operatore' => $record['operatore'],
                'articolo' => $record['articolo'],
                'cartellino' => $record['cartellino'],
                'commessa' => $record['commessa'],
                'calzata' => $record['calzata']
            ];
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'Record not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>

<?php
require_once '../../config/config.php';
if (isset($_GET['testid'])) {
    $testid = $_GET['testid'];
    try {
        // Inizializza la connessione al database usando PDO
        $pdo = getDbInstance();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Recupera il record dalla tabella cq_records
        $stmt = $pdo->prepare("SELECT * FROM cq_records WHERE testid = :testid");
        $stmt->execute(['testid' => $testid]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($record) {
            // Recupera il campo note dal record
            $note = $record['note'];
           
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
        echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
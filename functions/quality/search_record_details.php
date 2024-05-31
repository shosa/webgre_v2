<?php
require_once '../../config/config.php';

if (isset($_GET['testid'])) {
    $testid = $_GET['testid'];

    try {
        // Inizializza la connessione al database usando PDO
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Recupera il record dalla tabella cq_records
        $stmt = $pdo->prepare("SELECT * FROM cq_records WHERE testid = :testid");
        $stmt->execute(['testid' => $testid]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            // Recupera il campo note dal record
            $note = $record['note'];

            // Recupera il nome dell'operatore dalla tabella utenti
            $stmt = $pdo->prepare("SELECT Nome FROM utenti WHERE user_name = :operatore");
            $stmt->execute(['operatore' => $record['operatore']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

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
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>

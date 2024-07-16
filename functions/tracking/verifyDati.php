<?php

require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commessa = $_POST['commessa'];

    try {
        $db = getDbInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM dati WHERE Cartel = :commessa');
        $stmt->execute(['commessa' => $commessa]);
        $exists = $stmt->fetchColumn() > 0;

        echo json_encode(['exists' => $exists]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
<?php
require_once '../../config/config.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

if (!isset($_POST['table']) || empty($_POST['table']) || !isset($_POST['id'])) {
    echo json_encode(['error' => 'Parametri mancanti']);
    exit;
}

$table = $_POST['table'];
$id = $_POST['id'];

// Semplice validazione del nome tabella (per prevenire SQL injection)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    echo json_encode(['error' => 'Nome tabella non valido']);
    exit;
}

try {
    $db = getDbInstance();
    
    // Get primary key column
    $stmt = $db->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    $primaryKey = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$primaryKey) {
        echo json_encode(['error' => 'Chiave primaria non trovata']);
        exit;
    }
    
    $primaryKeyColumn = $primaryKey['Column_name'];
    
    // Prepare and execute delete query
    $query = "DELETE FROM `$table` WHERE `$primaryKeyColumn` = :id";
    $stmt = $db->prepare($query);
    $stmt->execute(['id' => $id]);
    
    $rowCount = $stmt->rowCount();
    
    if ($rowCount > 0) {
        echo json_encode(['success' => true, 'message' => 'Record eliminato con successo']);
    } else {
        echo json_encode(['error' => 'Nessun record trovato con ID: ' . $id]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore nell\'eliminazione del record: ' . $e->getMessage()]);
}
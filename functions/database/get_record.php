<?php
require_once '../../config/config.php';

if (!isset($_GET['table']) || empty($_GET['table']) || !isset($_GET['id'])) {
    echo json_encode(['error' => 'Parametri mancanti']);
    exit;
}

$table = $_GET['table'];
$id = $_GET['id'];

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
    
    // Get record data
    $query = "SELECT * FROM `$table` WHERE `$primaryKeyColumn` = :id";
    $stmt = $db->prepare($query);
    $stmt->execute(['id' => $id]);
    
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        echo json_encode(['error' => 'Record non trovato']);
        exit;
    }
    
    echo json_encode(['success' => true, 'data' => $record]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore nel recupero del record: ' . $e->getMessage()]);
}
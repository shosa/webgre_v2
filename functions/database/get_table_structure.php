<?php
require_once '../../config/config.php';


if (!isset($_GET['table']) || empty($_GET['table'])) {
    echo json_encode(['error' => 'Nome tabella non specificato']);
    exit;
}

$table = $_GET['table'];

// Semplice validazione del nome tabella (per prevenire SQL injection)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    echo json_encode(['error' => 'Nome tabella non valido']);
    exit;
}

try {
    $db = getDbInstance();
    
    // Get table structure
    $stmt = $db->query("DESCRIBE `$table`");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($columns);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore nel recupero della struttura della tabella: ' . $e->getMessage()]);
}
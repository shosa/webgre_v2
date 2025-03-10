<?php
require_once '../../config/config.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

if (!isset($_POST['table']) || empty($_POST['table']) || !isset($_POST['data'])) {
    echo json_encode(['error' => 'Parametri mancanti']);
    exit;
}

$table = $_POST['table'];
$jsonData = $_POST['data'];

// Semplice validazione del nome tabella (per prevenire SQL injection)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    echo json_encode(['error' => 'Nome tabella non valido']);
    exit;
}

try {
    $data = json_decode($jsonData, true);
    
    if (!is_array($data)) {
        echo json_encode(['error' => 'Dati non validi']);
        exit;
    }
    
    $db = getDbInstance();
    
    // Prepare column names and placeholders
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');
    
    $columnsStr = '`' . implode('`, `', $columns) . '`';
    $placeholdersStr = implode(', ', $placeholders);
    
    // Prepare and execute query
    $query = "INSERT INTO `$table` ($columnsStr) VALUES ($placeholdersStr)";
    $stmt = $db->prepare($query);
    
    // Execute with values
    $stmt->execute(array_values($data));
    
    echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore nell\'aggiunta del record: ' . $e->getMessage()]);
}
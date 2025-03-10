<?php
require_once '../../config/config.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

if (!isset($_POST['table']) || empty($_POST['table']) || !isset($_POST['operation'])) {
    echo json_encode(['error' => 'Parametri mancanti']);
    exit;
}

$table = $_POST['table'];
$operation = $_POST['operation'];

// Semplice validazione del nome tabella (per prevenire SQL injection)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    echo json_encode(['error' => 'Nome tabella non valido']);
    exit;
}

// Validate operation
$allowedOperations = ['optimize', 'repair', 'truncate'];
if (!in_array($operation, $allowedOperations)) {
    echo json_encode(['error' => 'Operazione non valida']);
    exit;
}

try {
    $db = getDbInstance();
    
    switch ($operation) {
        case 'optimize':
            $stmt = $db->query("OPTIMIZE TABLE `$table`");
            break;
            
        case 'repair':
            $stmt = $db->query("REPAIR TABLE `$table`");
            break;
            
        case 'truncate':
            $stmt = $db->query("TRUNCATE TABLE `$table`");
            break;
    }
    
    echo json_encode(['success' => true, 'message' => 'Operazione completata con successo']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore durante l\'operazione: ' . $e->getMessage()]);
}
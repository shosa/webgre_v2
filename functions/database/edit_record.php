<?php
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

if (!isset($_POST['table']) || empty($_POST['table']) || !isset($_POST['id']) || !isset($_POST['data'])) {
    echo json_encode(['error' => 'Parametri mancanti']);
    exit;
}

$table = $_POST['table'];
$id = $_POST['id'];
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
    
    // Get primary key column
    $stmt = $db->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    $primaryKey = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$primaryKey) {
        echo json_encode(['error' => 'Chiave primaria non trovata']);
        exit;
    }
    
    $primaryKeyColumn = $primaryKey['Column_name'];
    
    // Remove primary key from data if it exists
    if (isset($data[$primaryKeyColumn])) {
        unset($data[$primaryKeyColumn]);
    }
    
    // Prepare SET clause
    $setClause = '';
    $params = [];
    
    foreach ($data as $column => $value) {
        if (!empty($setClause)) {
            $setClause .= ', ';
        }
        
        $setClause .= "`$column` = :$column";
        $params[$column] = $value;
    }
    
    // Add primary key to parameters
    $params['primary_key'] = $id;
    
    // Prepare and execute update query
    $query = "UPDATE `$table` SET $setClause WHERE `$primaryKeyColumn` = :primary_key";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $rowCount = $stmt->rowCount();
    
    if ($rowCount > 0) {
        echo json_encode(['success' => true, 'message' => 'Record aggiornato con successo']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Nessuna modifica effettuata']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore nella modifica del record: ' . $e->getMessage()]);
}
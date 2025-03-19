<?php
// File: get_query.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';

// Validazione dell'input
if (!isset($_POST['query_id']) || !is_numeric($_POST['query_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'ID query non valido']);
    exit;
}

$query_id = intval($_POST['query_id']);

try {
    // Connessione al database
    $conn = getDbInstance();
    
    // Utilizzo di una query preparata per prevenire SQL injection
    $sql = "SELECT text_query FROM activity_log WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $query_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && isset($result['text_query'])) {
        // Log dell'accesso
        $log_sql = "INSERT INTO activity_log (user_id, category, activity_type, description, note) 
                    VALUES (:user_id, 'Admin', 'View', 'Visualizzazione dettagli query', :note)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $note = "Visualizzazione della query con ID: " . $query_id;
        $log_stmt->bindParam(':note', $note);
        $log_stmt->execute();
        
        // Invia i dati della query
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'query' => $result['text_query']]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Query non trovata']);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Errore database: ' . $e->getMessage()]);
}
?>
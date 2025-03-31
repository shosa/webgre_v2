<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';

header('Content-Type: application/json');

$pdo = getDbInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Ottieni tutti i cartellini con conteggio eccezioni
    $stmt = $pdo->query("
        SELECT 
            r.*,
            (SELECT COUNT(*) 
             FROM cq_hermes_eccezioni e 
             WHERE e.cartellino_id = r.id) AS eccezioni_count,
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM cq_hermes_eccezioni e 
                    WHERE e.cartellino_id = r.id
                ) THEN 1 
                ELSE 0 
            END AS ha_eccezioni
        FROM 
            cq_hermes_records r
        ORDER BY 
            r.data_controllo DESC
    ");
    
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatta le date per la visualizzazione
    foreach ($records as &$record) {
        // Formattazione data
        $record['data_controllo'] = date('d/m/Y H:i', strtotime($record['data_controllo']));
        
        // Converti ha_eccezioni a booleano per la compatibilità JavaScript
        $record['ha_eccezioni'] = (bool)$record['ha_eccezioni'];
        
        // Converti eccezioni_count a intero
        $record['eccezioni_count'] = intval($record['eccezioni_count']);
    }
    
    echo json_encode($records);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore database: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
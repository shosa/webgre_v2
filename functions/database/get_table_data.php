<?php
session_start();
require_once '../../config/config.php';

if (isset($_GET['table'])) {
    $tableName = $_GET['table'];
    
    try {
        $pdo = getDbInstance();
        $stmt = $pdo->query("SELECT * FROM $tableName");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>

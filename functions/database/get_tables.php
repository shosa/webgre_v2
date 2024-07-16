<?php
session_start();
require_once '../../config/config.php';

try {
    $pdo = getDbInstance();
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_NUM);
    $tableNames = array_map(function($table) {
        return $table[0];
    }, $tables);
    echo json_encode($tableNames);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

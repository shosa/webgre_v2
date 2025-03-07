<?php
session_start();
require_once '../../config/config.php';
if (isset($_POST['query'])) {
    $query = $_POST['query'];
    try {
        $pdo = getDbInstance();
        $stmt = $pdo->query($query);
        if (stripos(trim($query), 'SELECT') === 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columns = array_keys($result[0]);
            echo json_encode(['columns' => $columns, 'data' => $result]);
        } else {
            echo json_encode(['success' => 'Query eseguita con successo!']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>

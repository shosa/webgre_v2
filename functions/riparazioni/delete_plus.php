<?php
session_start();
require_once '../../utils/log_utils.php';
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['ids'];
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM riparazioni WHERE IDRIP IN ($placeholders)";
        $statement = $pdo->prepare($sql);
        $statement->execute($ids);
    }
}


?>
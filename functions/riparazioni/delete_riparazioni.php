<?php
session_start();
require_once '../../utils/log_utils.php';
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

$del_id = filter_input(INPUT_POST, 'del_id');

if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $riparazione_id = $del_id;

    try {
        $pdo = getDbInstance();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $delete_query = "DELETE FROM riparazioni WHERE IDRIP = :idrip";
        $stmt = $pdo->prepare($delete_query);
        $stmt->bindParam(':idrip', $riparazione_id, PDO::PARAM_INT);
        $stmt->execute();
        $real_query = replacePlaceholders($pdo, $delete_query, $riparazione_id);
        logActivity($_SESSION['user_id'], 'RIPARAZIONI', 'ELIMINA', 'Rimossa Cedola ', '#' . $del_id, '');
        $_SESSION['info'] = "Riparazione cancellata!";
        header('Location: riparazioni');
        exit;
    } catch (PDOException $e) {
        $_SESSION['failure'] = "Impossibile eliminare!";
        header('Location: riparazioni');
        exit;
    }
}
?>
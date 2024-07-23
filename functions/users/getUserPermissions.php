<?php
require_once '../../config/config.php';

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $pdo = getDbInstance();
    $stmt = $pdo->prepare("SELECT * FROM permessi WHERE id_utente = :id_utente");
    $stmt->bindParam(':id_utente', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $permissions = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($permissions);
}
?>
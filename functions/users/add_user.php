<?php
require_once '../../config/config.php';
$pdo = getDbInstance();

$user_name = $_POST['user_name'];
$nome = $_POST['nome'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$admin_type = $_POST['admin_type'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO utenti (user_name, nome, password, admin_type) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_name, $nome, $password, $admin_type]);

    $user_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO permessi (id_utente) VALUES (?)");
    $stmt->execute([$user_id]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
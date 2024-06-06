<?php
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $username = $_POST['user_name'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $ruolo = $_POST['admin_type'];

    $pdo = getDbInstance();
    $stmt = $pdo->prepare("UPDATE utenti SET user_name = ?, nome = ?, email = ?, admin_type = ? WHERE id = ?");
    $stmt->execute([$username, $nome, $email, $ruolo, $id]);


}
?>
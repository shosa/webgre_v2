<?php

require_once '../../config/config.php';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'];

    $username = $_POST['user_name'];

    $nome = $_POST['nome'];

    $mail = $_POST['mail'];

    $ruolo = $_POST['admin_type'];



    $pdo = getDbInstance();

    $stmt = $pdo->prepare("UPDATE utenti SET user_name = ?, nome = ?, mail = ?, admin_type = ? WHERE id = ?");

    $stmt->execute([$username, $nome, $mail, $ruolo, $id]);





}

?>
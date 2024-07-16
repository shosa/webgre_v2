<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';

// Controlla se il modulo di aggiunta è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Processa i dati del modulo di aggiunta
    $user_name = $_POST['user_name'];
    $nome = $_POST['nome'];
    $password = $_POST['password']; // Assicurati di trattare correttamente la password per la sicurezza
    $admin_type = $_POST['admin_type'];

    // Connessione al database utilizzando PDO
    $pdo = getDbInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query per aggiungere l'utente al database
    $stmt = $pdo->prepare("INSERT INTO utenti (user_name, nome, password, admin_type) VALUES (:user_name, :nome, :password, :admin_type)");
    $stmt->bindParam(':user_name', $user_name);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':password', password_hash($password, PASSWORD_DEFAULT)); // Hash della password per sicurezza
    $stmt->bindParam(':admin_type', $admin_type);
    $stmt->execute();

    // Reindirizza alla pagina degli utenti con un messaggio di successo
    $_SESSION['success'] = 'Utente aggiunto con successo.';
    header('location: manageUsers');
    exit;
} else {
    // Se il modulo di aggiunta non è stato inviato, reindirizza alla home page
    header('location: ../../index.php');
    exit;
}
?>
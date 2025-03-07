<?php
require_once '../../config/config.php';
$pdo = getDbInstance();
// Recupera e valida i dati inviati
$user_name = filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_STRING);
$nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
$admin_type = filter_input(INPUT_POST, 'admin_type', FILTER_SANITIZE_STRING);
if (empty($user_name) || empty($nome) || empty($password) || empty($admin_type)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Tutti i campi sono obbligatori.', 
        'data' => [
            'user_name' => $user_name,
            'nome' => $nome,
            'password' => $password,
            'admin_type' => $admin_type
        ]
    ]);
    exit;
}
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO utenti (user_name, nome, password, admin_type) VALUES (:user_name, :nome, :password, :admin_type)");
    $stmt->bindParam(':user_name', $user_name);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':admin_type', $admin_type);
    if (!$stmt->execute()) {
        throw new Exception("Errore SQL: " . implode(" - ", $stmt->errorInfo()));
    }
    $user_id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("INSERT INTO permessi (id_utente) VALUES (:user_id)");
    $stmt->bindParam(':user_id', $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Errore SQL: " . implode(" - ", $stmt->errorInfo()));
    }
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log($e->getMessage());  // Logga l'errore
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

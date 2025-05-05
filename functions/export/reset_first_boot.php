<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Recupera il progressivo dalla richiesta GET
$progressivo = $_GET['progressivo'];

// Recupera l'istanza del database (PDO)
$db = getDbInstance();

try {
    $stmt = $db->prepare("UPDATE exp_documenti SET first_boot = 0 WHERE id = :id");
    $stmt->execute([':id' => $progressivo]);
} catch (PDOException $e) {
    // In caso di errore, log o gestione personalizzata
    die("Errore nell'aggiornamento: " . htmlspecialchars($e->getMessage()));
}

// Redirect alla pagina di dettaglio del documento
header("Location: continue_ddt.php?progressivo=$progressivo");
exit;

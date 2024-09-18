<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
session_start();
require_once "../../config/config.php";
require_once BASE_PATH . "/utils/helpers.php";
require_once BASE_PATH . "/utils/log_utils.php";
// Verifica se l'ID del modello è passato tramite POST
if (!isset($_POST['model_id'])) {
    $_SESSION['error'] = "ID del modello non fornito.";
    header('Location: ../../index');
    exit();
}
$modelId = $_POST['model_id'];
$consegna = isset($_POST['consegna']) ? $_POST['consegna'] : null;
$pdo = getDbInstance();
// Verifica se l'utente ha richiesto la rimozione dell'immagine
if (isset($_POST['remove_immagine']) && $_POST['remove_immagine'] == '1') {
    // Ottieni il percorso dell'immagine
    $stmt = $pdo->prepare("SELECT immagine FROM samples_modelli WHERE id = :model_id");
    $stmt->execute(['model_id' => $modelId]);
    $model = $stmt->fetch(PDO::FETCH_ASSOC);
    $immaginePath = $model['immagine'];
    // Rimuovi l'immagine se esiste
    if (!empty($immaginePath) && file_exists("../../functions/samples/img/$immaginePath")) {
        unlink("../../functions/samples/img/$immaginePath");
    }
    // Aggiorna il percorso dell'immagine nel database
    $stmt = $pdo->prepare("UPDATE samples_modelli SET immagine = NULL WHERE id = :model_id");
    $stmt->execute(['model_id' => $modelId]);
}
// Aggiorna i dettagli del modello, inclusa la data di consegna
$stmt = $pdo->prepare("UPDATE samples_modelli SET nome_modello = :nome_modello, variante = :variante, forma = :forma, note = :note, consegna = :consegna WHERE id = :model_id");
$stmt->execute([
    'nome_modello' => $_POST['nome_modello'],
    'variante' => $_POST['variante'],
    'note' => $_POST['note'],
    'forma' => $_POST['forma'],
    'consegna' => $consegna,
    'model_id' => $modelId
]);
// Verifica se è stato caricato un nuovo file immagine
if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === UPLOAD_ERR_OK) {
    $immagineTempPath = $_FILES['immagine']['tmp_name'];
    $immagineName = $_FILES['immagine']['name'];
    move_uploaded_file($immagineTempPath, "../../functions/samples/img/$immagineName");
    // Aggiorna il percorso dell'immagine nel database
    $stmt = $pdo->prepare("UPDATE samples_modelli SET immagine = :immagine WHERE id = :model_id");
    $stmt->execute(['immagine' => $immagineName, 'model_id' => $modelId]);
}
$stmt = $pdo->prepare("UPDATE samples_modelli SET notify_edits = 1 WHERE id = :modelId");
$stmt->bindParam(':modelId', $modelId);
$stmt->execute();
logActivity($_SESSION['user_id'], 'CAMPIONARIO', 'MODIFICA', 'Anagrafica e Dettagli', 'ID: ' . $modelId);
$_SESSION['success'] = "Modello aggiornato con successo.";
header('Location: editDiba.php?model_id=' . $modelId);
exit();
?>
<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
session_start();
require_once "../../config/config.php";
require_once "../../utils/helpers.php";
require_once "../../utils/log_utils.php";

// Verifica se l'ID del modello e l'azione sono passati tramite GET
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "ID del modello o azione non forniti."]);
    exit();
}

$modelId = (int) $_GET['id'];
$action = $_GET['action'];

$actions = [
  
    'TAGLIO' => ['label' => 'Taglio', 'column' => 'stato_taglio', 'date_column' => 'data_taglio'],
  
    'ORLATURA' => ['label' => 'Orlatura', 'column' => 'stato_orlatura', 'date_column' => 'data_orlatura'],
   
    'MONTAGGIO' => ['label' => 'Montaggio', 'column' => 'stato_montaggio', 'date_column' => 'data_montaggio'],

    'SPEDITO' => ['label' => 'Spedito', 'column' => 'stato_spedito', 'date_column' => 'data_spedito'],
];
if (!array_key_exists($action, $actions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "Azione non valida."]);
    exit();
}

$pdo = getDbInstance();
$stmt = $pdo->prepare("SELECT * FROM samples_avanzamenti WHERE modello_id = :model_id");
$stmt->execute(['model_id' => $modelId]);
$avanzamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$avanzamento) {
    // Se non esiste ancora un record, creane uno nuovo
    $stmt = $pdo->prepare("INSERT INTO samples_avanzamenti (modello_id) VALUES (:model_id)");
    $stmt->execute(['model_id' => $modelId]);
    $stmt = $pdo->prepare("SELECT * FROM samples_avanzamenti WHERE modello_id = :model_id");
    $stmt->execute(['model_id' => $modelId]);
    $avanzamento = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Verifica l'ordine delle fasi
$canAdvance = true;
$missingPhase = null;
foreach ($actions as $key => $value) {
    if ($key == $action)
        break;
    if (!$avanzamento[$value['column']]) {
        $canAdvance = false;
        $missingPhase = $key;
        break;
    }
}

if ($avanzamento[$actions[$action]['column']]) {
    http_response_code(409); // Conflitto
    echo json_encode(['success' => false, 'message' => "La fase $action è già completata."]);
    exit();
}

if (!$canAdvance) {
    // Aggiorna la fase mancante se viene saltata
    $stmt = $pdo->prepare("UPDATE samples_avanzamenti SET " . $actions[$missingPhase]['column'] . " = 1, " . $actions[$missingPhase]['date_column'] . " = NOW() WHERE modello_id = :model_id");
    $stmt->execute(['model_id' => $modelId]);
    $_SESSION['warning'] = "La fase $missingPhase non era completata. È stata completata automaticamente.";
}

// Aggiorna lo stato e la data dell'azione richiesta
$stmt = $pdo->prepare("UPDATE samples_avanzamenti SET " . $actions[$action]['column'] . " = 1, " . $actions[$action]['date_column'] . " = NOW() WHERE modello_id = :model_id");
$stmt->execute(['model_id' => $modelId]);

http_response_code(200);
echo json_encode(['success' => true, 'message' => "Avanzamento per la fase $action completato."]);
exit();
?>
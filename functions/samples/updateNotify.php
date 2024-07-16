<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once "../../config/config.php";
require_once "../../utils/helpers.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo = getDbInstance();
    $modelId = $_POST['model_id'];
    $notifyEdits = $_POST['notify_edits'];

    $stmt = $pdo->prepare("UPDATE samples_modelli SET notify_edits = :notify_edits WHERE id = :model_id");
    $stmt->execute(['notify_edits' => $notifyEdits, 'model_id' => $modelId]);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

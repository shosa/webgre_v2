<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
require_once BASE_PATH . '/vendor/autoload.php';

$db = getDbInstance();

$progressivo = $_POST['progressivo'] ?? null;
$azione = $_POST['azione'] ?? null;

switch ($azione) {
    case 'get':
        getAutorizzazione($progressivo, $db);
        break;
    case 'save':
        $data = json_decode($_POST['data'], true);
        saveAutorizzazione($progressivo, $data, $db);
        break;
    case 'reset':
        resetAutorizzazione($progressivo, $db);
        break;
    default:
        echo 'Azione non valida';
        break;
}

function getAutorizzazione($progressivo, $db)
{
    $db->where('id_documento', $progressivo);
    $data = $db->getOne('exp_piede_documenti', 'autorizzazione');
    if ($db->count > 0) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Dati non trovati']);
    }
}

function saveAutorizzazione($progressivo, $data, $db)
{
    $db->where('id_documento', $progressivo);
    $result = $db->update('exp_piede_documenti', $data);
    if ($result) {
        echo 'Dati salvati con successo';
    } else {
        echo 'Salvataggio fallito: ' . $db->getLastError();
    }
}

function resetAutorizzazione($progressivo, $db)
{
    $data = ['autorizzazione' => ''];
    $db->where('id_documento', $progressivo);
    $result = $db->update('exp_piede_documenti', $data);
    if ($result) {
        echo 'Dati ripristinati con successo';
    } else {
        echo 'Ripristino fallito: ' . $db->getLastError();
    }
}
?>

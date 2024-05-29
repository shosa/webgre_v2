<?php
session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/vendor/autoload.php';

use MysqliDb\MysqliDb;

$db = getDbInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $azione = $_POST['azione'];

    if ($azione === 'elimina') {
        $tabella = $_POST['tabella'];
        $id = $_POST['id'];
        $db->where('id', $id);
        $db->delete($tabella);
    } elseif ($azione === 'inserisci') {
        $tabella = $_POST['tabella'];
        $dati = $_POST;
        unset($dati['azione'], $dati['tabella']);
        $idInserito = $db->insert($tabella, $dati);
    } elseif ($azione === 'modifica') {
        $tabella = $_POST['tabella'];
        $id = $_POST['id'];
        $dati = $_POST;
        unset($dati['azione'], $dati['tabella'], $dati['id']);
        $db->where('id', $id);
        $db->update($tabella, $dati);
    } elseif ($azione === 'ottieni_righe') {
        $tabella = $_POST['tabella'];
        $righe = $db->get($tabella);
        echo json_encode($righe); // Restituisci le righe della tabella come JSON
        exit(); // Interrompi l'esecuzione dello script
    }
}

$db->disconnect();
header('Location: ' . $_SERVER['HTTP_REFERER']); // Redirect alla pagina precedente
exit();

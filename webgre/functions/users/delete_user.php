<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';
$del_id = filter_input(INPUT_POST, 'del_id');
$db = getDbInstance();

if ($_SESSION['admin_type'] != 'super') {
    header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized");
}


// Delete a user using user_id
if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $db->where('id', $del_id);
    $stat = $db->delete('utenti');
    if ($stat) {
        $_SESSION['info'] = "Utente eliminato correttamente!";
        header('location: admin_users.php');
        exit;
    }
}
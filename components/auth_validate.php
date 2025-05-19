<?php
// Includi url.php usando BASE_PATH
require_once(dirname(__DIR__) . '/config/url.php');

// Continua con il resto del codice di autenticazione
if (!isset($_SESSION['user_logged_in']) && basename($_SERVER['PHP_SELF']) !== 'login') {
    header('Location: ' . BASE_URL . '/login');
    exit;

// Continua con il resto del codice di autenticazione
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login') {
    header('Location: ' . BASE_URL . '/login');
    exit;
}
?>

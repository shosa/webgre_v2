<?php
// Includi url.php usando BASE_PATH
require_once(dirname(__DIR__) . '/config/url.php');

// Continua con il resto del codice di autenticazione
if (!isset($_SESSION['user_logged_in']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
?>

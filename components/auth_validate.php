<?php
if (!isset($_SESSION['user_logged_in']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login');
    // $_SESSION['warning'] = "Sessione Scaduta. Effettuare nuovamente l'accesso";
    exit;
}
/*if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: ../../login.php');
    $_SESSION['warning'] = "Sessione Scaduta. Effettuare nuovamente l'accesso";
    exit;
}*/
?>
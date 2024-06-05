<?php
// If User is logged in the session['user_logged_in'] will be set to true

// If user is Not Logged in and the current page is not login.php, redirect to login.php page.
if (!isset($_SESSION['user_logged_in']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: ../../login.php');
    $_SESSION['warning'] = "Sessione Scaduta. Effettuare nuovamente l'accesso";
    exit;
}
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: ../../login.php');
    $_SESSION['warning'] = "Sessione Scaduta. Effettuare nuovamente l'accesso";
    exit;
}
?>
<?php
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . '</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['danger'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['danger'], ENT_QUOTES, 'UTF-8') . '</div>';
    unset($_SESSION['danger']);
}
if (isset($_SESSION['warning'])) {
    echo '<div class="alert alert-warning">' . htmlspecialchars($_SESSION['warning'], ENT_QUOTES, 'UTF-8') . '</div>';
    unset($_SESSION['warning']);
}
if (isset($_SESSION['info'])) {
    echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['info'], ENT_QUOTES, 'UTF-8') . '</div>';
    unset($_SESSION['info']);
}
if (isset($_SESSION['critical'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['critical'], ENT_QUOTES, 'UTF-8') . '</div>';
    unset($_SESSION['critical']);
}
?>
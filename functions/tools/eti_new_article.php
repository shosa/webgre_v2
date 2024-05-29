<?php
// Nel tuo file PHP di destinazione (es. insert_article.php)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cm']) && isset($_POST['art']) && isset($_POST['des'])) {
    $cm = $_POST['cm'];
    $art = $_POST['art'];
    $des = $_POST['des'];

    // Implementa qui la logica di inserimento nel database
    // Utilizza prepared statements per prevenire SQL injection

    echo "Articolo aggiunto con successo!";
}
?>
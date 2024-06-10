<?php
session_start();

// Assicurati che la sessione sia avviata prima di accedere ai suoi contenuti
if (session_status() === PHP_SESSION_ACTIVE) {
    // Verifica se la variabile di sessione è impostata e non è vuota
    if (isset($_SESSION) && !empty($_SESSION)) {
        // Stampa tutti i contenuti della variabile di sessione
        echo "Contenuti della variabile \$_SESSION:<br>";
        foreach ($_SESSION as $key => $value) {
            echo "$key => $value<br>";
        }
        echo "Contenuti della variabile \$_SERVER:<br>";
        foreach ($_SERVER as $key => $value) {
            echo "$key => $value<br>";
        }
    } else {
        echo "La variabile \$_SESSION è vuota o non è impostata.";
    }
} else {
    echo "La sessione non è attiva.";
}
?>

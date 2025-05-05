<?php
/*
// Funzione personalizzata per gestire gli errori
function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    // Ottieni il nome della pagina corrente
    $currentPage = basename($_SERVER['PHP_SELF']);

    // Ottieni l'orario corrente
    $errorTime = date('Y-m-d H:i:s');

    // Crea un messaggio di errore dettagliato
    $errorMessage = "\n[$errorTime] [PAGINA: $currentPage] [ERRORE $errno] $errstr in $errfile nella riga $errline \n\n";

    // Logga l'errore in un file
    error_log($errorMessage, 3, __DIR__ . '/error_log.txt');

    // Mostra un messaggio di errore personalizzato all'utente (opzionale)
    if ($errno != E_NOTICE) { // Puoi decidere quali errori mostrare
        echo "<div class='alert alert-danger'>Qualcosa è andato storto. Riprova più tardi.</div>";
    }
    $_SESSION["critical"] = "Si è verificato un errore, contattare l'amministratore!";
    // Impedisci la gestione predefinita dell'errore di PHP
    return true;
}

// Funzione personalizzata per gestire le eccezioni
function customExceptionHandler($exception)
{
    // Ottieni il nome della pagina corrente
    $currentPage = basename($_SERVER['PHP_SELF']);

    // Ottieni l'orario corrente
    $errorTime = date('Y-m-d H:i:s');

    $errorMessage = "\n[$errorTime] [PAGINA: $currentPage] ERRORE: " . $exception->getMessage();

    error_log($errorMessage, 3, __DIR__ . '/error_log.txt');

    echo "<div class='alert alert-danger'>Si è verificato un problema. Riprova più tardi.</div>";
}

// Registra la funzione come gestore degli errori
set_error_handler("customErrorHandler");

// Registra il gestore delle eccezioni
set_exception_handler("customExceptionHandler");

// Imposta il log degli errori nel file PHP
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// Opzionalmente, imposta il livello di error reporting (puoi scegliere il livello desiderato)
error_reporting(E_ALL); // Per mostrare tutti gli errori
?>
*/
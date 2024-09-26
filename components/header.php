<?php
require_once(__DIR__ . '/error_handler.php');
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="manifest" href="<?php echo BASE_URL; ?>/manifest.json">
    <title>WEBGRE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/css/sb-admin-2.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>
<script>
    window.onerror = function (message, source, lineno, colno, error) {
        // Ottieni il nome della pagina
        var currentPage = window.location.pathname.split("/").pop();

        // Ottieni l'orario corrente
        var errorTime = new Date().toLocaleString();

        // Crea un oggetto con i dettagli dell'errore
        var errorData = {
            time: errorTime,
            page: currentPage,
            message: message,
            source: source,
            lineno: lineno,
            colno: colno,
            error: error ? error.stack : ''
        };

        // Invia l'errore al server (tramite una richiesta AJAX)
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo BASE_URL; ?>/components/log_js_error.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify(errorData));

        // Mostra un messaggio all'utente (opzionale)
        alert('Si è verificato un problema. Riprova più tardi.');
    };
</script>
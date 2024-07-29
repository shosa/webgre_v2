<?php
include ("config/config.php");
http_response_code(403); // Imposta il codice di risposta HTTP a 404

// Messaggio predefinito per la pagina non trovata
$message = "Accesso non autorizzato.";

// Verifica se è stato passato un parametro 'message' tramite GET
if (isset($_GET['message'])) {
    // Gestisci diversi casi di messaggio
    switch ($_GET['message']) {
        case "SampleUpdateAvanzamento":
            $message = "Modello non trovato!";
            break;
        // Aggiungi altri casi se necessario
    }
}
?>

<!DOCTYPE html>
<html lang="it">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="<?php echo BASE_URL ?>/css/sb-admin-2.css" rel="stylesheet">
    <title>Errore</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            text-align: center;
            padding: 50px;
        }

        h1 {
            font-size: 5rem;
            margin-bottom: 10px;
            color:black;
        }

        p {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div>
        <h1 class="text-dark font-weight-bold">403</h1>
        <div class="alert alert-danger"><?php echo $message; ?></div>
      
        <a class="btn btn-primary" href="<?php echo BASE_URL ?>">HOME PAGE</a>
    </div>
</body>

</html>
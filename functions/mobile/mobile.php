<?php require_once '../../config/config.php'; ?>
<!DOCTYPE html>
<html lang="it">
<?php
include BASE_PATH . '/includes/mobile_header.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Web G.R.E</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            margin-top: 0px;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 5px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f5f5f5;
        }

        button {
            display: block;
            margin: 10px auto;
            padding: 10px 20px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 40pt;
        }
    </style>
</head>

<body>
    <?php
    // Ottieni l'ID della riparazione dalla richiesta POST
    $idrip = filter_input(INPUT_GET, 'idrip', FILTER_SANITIZE_NUMBER_INT);

    // Verifica se l'ID della riparazione è valido
    if ($idrip) {
        // Esegui una query per ottenere i dettagli della riparazione dal tuo database
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SELECT * FROM riparazioni WHERE IDRIP = $idrip";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {
            $record = mysqli_fetch_assoc($result);
            echo '<h2>Dettaglio</h2>';

            echo '<table class="table table-bordered">';
            echo '<tr><th style="width:27%">ID:</th><th style="width:1%;background-color:#c0e7fc;text-shadow: -3px 2px 7px rgba(0,0,0,0.33);"></th><td><b>' . $record['IDRIP'] . '</b></td></tr>';
            echo '</table>';

            echo '<table class="table table-bordered">';
            echo '<tr><th style="width:27%">Linea:</th><th style="width:1%;background-color:#cafcc0;text-shadow: -3px 2px 7px rgba(0,0,0,0.33);"></th><td>' . $record['LINEA'] . '</td></tr>';
            echo '<tr><th style="width:27%">Utente:</th><th style="width:1%;background-color:#cafcc0;text-shadow: -3px 2px 7px rgba(0,0,0,0.33);"></th><td>' . $record['UTENTE'] . '</td></tr>';
            echo '</table>';

            echo '<table class="table table-bordered">';
            echo '<tr><th style="width:27%">Codice:</th><th style="width:1%;background-color:#fce2c0;text-shadow: -3px 2px 7px rgba(0,0,0,0.33);"></th><td>' . $record['CODICE'] . '</td></tr>';
            echo '<tr><th style="width:27%">Descrizione:</th><th style="width:1%;background-color:#fce2c0;text-shadow: -3px 2px 7px rgba(0,0,0,0.33);"></th><td>' . $record['ARTICOLO'] . '</td></tr>';
            echo '<tr><th style="width:27%">Paia:</th><th style="width:1%;background-color:#fce2c0;text-shadow: -3px 2px 7px rgba(0,0,0,0.33);"></th><td>' . $record['QTA'] . '</td></tr>';
            echo '</table>';

            echo '<table class="table table-bordered">';
            echo '<tr><th style="width:27%">Urgenza:</th><th style="width:1%;background-color:#fcc0cb;text-shadow: -3px 2px 7px rgba(0,0,0,0.33);"></th><td>' . $record['URGENZA'] . '</td></tr>';
            echo '<tr><th style="width:27%">Reparto:</th><th style="width:1%;background-color:#fcc0cb;text-shadow: -3px 2px 7px rgba(0,0,0,0.33);"></th><td>' . $record['REPARTO'] . '</td></tr>';
            echo '<tr><th style="width:27%">Laboratorio:</th><th style="width:1%;background-color:#fcc0cb;text-shadow: -3px 2px 7px rgba(0,0,0,0.33);"></th><td>' . $record['LABORATORIO'] . '</td></tr>';
            echo '<tr><th style="width:27%">Data:</th><th style="width:1%;background-color:#fcc0cb;text-shadow: -3px 2px 7px rgba(0,0,0,0.33);"></th><td>' . $record['DATA'] . '</td></tr>';
            echo '</table>';

            echo '<table class="table table-bordered">';
            echo '<tr><th style="width:27%">Note:<th style="width:1%;background-color:#d7c0fc;text-shadow: -3px 2px 7px rgba(0,0,0,0.33);"></th><td>' . $record['CAUSALE'] . '</td></tr>';
            echo '</table>';

            // Pulsante per eliminare il record
            echo '<form method="post" action="delete.php" style="text-align: center;">';
            echo '<input type="hidden" name="idrip" value="' . $idrip . '">';
            echo '<button type="submit" class="btn btn-danger" style="font-size:40pt;"><i class="fad fa-trash-alt"></i></button>';
            echo '</form>';
        } else {
            echo 'Riparazione non trovata.';
        }

        mysqli_close($conn);
    } else {
        echo 'ID della riparazione non valido.';
    }
    ?>


    <!-- Aggiungi i tuoi script aggiuntivi qui -->

</body>

</html>
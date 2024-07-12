<?php
require_once '../../config/config.php';

// Ottieni l'ID del record dal parametro GET
$recordId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Verifica se l'ID del record è valido
if ($recordId) {
    try {
        // Connessione al database utilizzando PDO
        $pdo = getDbInstance();

        // Prepare SQL statement
        $statement = $pdo->prepare("SELECT * FROM riparazioni WHERE IDRIP = :idrip");

        // Bind the parameter
        $statement->bindParam(':idrip', $recordId);

        // Execute SQL statement
        $statement->execute();

        // Fetch the record
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            // Formatta i dettagli del record come desideri (ad esempio, in formato HTML)
            $details = '<table class="table table-condensed table-responsive table-bordered">';
            $details .= '<tr><th>ID:</th><td>' . $record['IDRIP'] . '</td></tr>';
            $details .= '<tr><th>LINEA:</th><td>' . $record['LINEA'] . '</td></tr>';
            $details .= '<tr><th>CODICE:</th><td>' . $record['CODICE'] . '</td></tr>';
            $details .= '<tr><th>ARTICOLO:</th><td>' . $record['ARTICOLO'] . '</td></tr>';
            $details .= '<tr><th>QTA:</th><td>' . $record['QTA'] . '</td></tr>';
            $details .= '<tr><th>REPARTO:</th><td>' . $record['REPARTO'] . '</td></tr>';
            $details .= '<tr><th>NOTE:</th><td>' . $record['CAUSALE'] . '</td></tr>';
            $details .= '<tr><th>LABORATORIO:</th><td>' . $record['LABORATORIO'] . '</td></tr>';
            $details .= '<tr><th>UTENTE:</th><td>' . $record['UTENTE'] . '</td></tr>';
            $details .= '<tr><th>DATA:</th><td>' . $record['DATA'] . '</td></tr>';
            $details .= '</table>';
            $details .= '<a href="file_preview.php?riparazione_id=' . $recordId . '" class="btn btn-block btn-warning btn-lg"><i
                class="fa-solid fa-print fa-lg"></i></a>';
            $details .= '<a href="#" class="btn btn-danger btn-block btn-lg delete_btn" data-toggle="modal"
            data-target="#confirm-delete-' . $recordId . '"><i class="fa-solid fa-trash-alt fa-lg"></i></a>';

            echo $details; // Restituisci i dettagli al chiamante
        } else {
            echo 'Record non trovato.';
        }
    } catch (PDOException $e) {
        // If an error occurs, display the error message
        echo "Errore: " . $e->getMessage();
    }
} else {
    echo 'ID del record non valido.';
}
?>
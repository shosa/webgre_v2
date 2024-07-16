<?php
header('Content-Type: application/json');
try {
    // Includi il file di configurazione e l'helper del database
    ob_clean(); // Pulisce l'output buffer

    require_once '../../config/config.php';
    $db = getDbInstance();

    // Recupera i dati dalla richiesta POST
    $deposito = isset($_POST['deposito']) ? $_POST['deposito'] : '';
    $codice_articolo = isset($_POST['codice_articolo']) ? $_POST['codice_articolo'] : '';
    $selected_des = isset($_POST['selected_des']) ? $_POST['selected_des'] : '';
    $isNumerata = isset($_POST['isNumerata']) ? $_POST['isNumerata'] : '';
    $valueNumerata = isset($_POST['valueNumerata']) ? $_POST['valueNumerata'] : '';

    // Recupera i dati specifici per l'opzione numerata
    $qta = $isNumerata ? array_map('floatval', explode(';', $_POST['qta'])) : $_POST['qta'];
    $num = $isNumerata ? array_map('floatval', explode(';', $_POST['num'])) : $_POST['num'];


    // ...


    // Recupera il valore di "cm" dalla tabella "inv_anagrafiche"
    $cmValue = $db->where('art', $codice_articolo)->getOne('inv_anagrafiche', 'cm');
    $existingData = $db->where('dep', $deposito)->where('art', $codice_articolo)->getOne('inv_list', 'qta');
    // Se hai un campo specifico per la quantità di "cm" nella tabella 'inv_anagrafiche', aggiungilo a $data.
// Altrimenti, aggiungi il valore a $data come segue:
    if ($existingData) {
        $existingQta = floatval(str_replace(',', '.', $existingData['qta'])); // Sostituisci la virgola con il punto
        $newQta = floatval(str_replace(',', '.', $_POST['qta'])); // Sostituisci la virgola con il punto

        // Formatta la nuova quantità con due decimali prima di sommarla


        $totalQta = $existingQta + $newQta;

        // Formatta la quantità totale con due decimali prima di aggiornare il database
        $totalQta = number_format($totalQta, 2, ',', ''); // Utilizza la virgola come separatore

        // Aggiorna la quantità nel database
        $updateData = array('qta' => $totalQta);
        $db->where('dep', $deposito)->where('art', $codice_articolo)->update('inv_list', $updateData);

        // Restituisci la risposta JSON
        echo json_encode(array('status' => 'warning', 'message' => 'Articolo <b>' . $_POST['codice_articolo'] . '</b> già presente, quantità sommata. Vecchia Qtà <b>' . $existingQta . '</b> + Nuova Qtà <b>' . $newQta . '</b> - TOTALE = <b>' . $totalQta . '</b>', 'query' => $db->getLastQuery()));
    } else {
        // L'articolo non esiste ancora nel deposito, inseriscilo normalmente
        $data = array(
            'dep' => $deposito,
            'art' => $codice_articolo,
            'des' => $selected_des,
            'qta' => $isNumerata ? array_sum($qta) : $qta,
            'num' => $num,
            'is_num' => $valueNumerata,
            'cm' => $cmValue['cm'] ?? null,
        );

        $insert = $db->insert('inv_list', $data);
        echo json_encode(array('status' => 'success', 'message' => 'Articolo <b>' . $codice_articolo . '</b> inserito con successo.', 'query' => $db->getLastQuery()));
        if ($insert) {
            // Invia una risposta JSON di successo

        } else {
            // Invia una risposta JSON di errore, includendo anche il messaggio di errore MySQL
            echo json_encode(array('status' => 'error', 'message' => 'Errore durante l\'inserimento del record.', 'error' => $db->getLastError(), 'query' => $db->getLastQuery()));
        }
    }

} catch (Exception $e) {
    // Stampa l'errore nel log o nella risposta JSON
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage(), 'html' => ob_get_contents()));
    ob_end_clean();
    die();

    // Aggiungi questo blocco per interrompere l'esecuzione e visualizzare la risposta completa
}
die();

?>
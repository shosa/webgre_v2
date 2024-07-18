<?php

header('Content-Type: application/json');

try {
    // Includi il file di configurazione e l'helper del database
    ob_clean(); // Pulisce l'output buffer

    require_once '../../config/config.php';

    // Crea la connessione PDO

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

    // Recupera il valore di "cm" dalla tabella "inv_anagrafiche"
    $stmt = $db->prepare("SELECT cm FROM inv_anagrafiche WHERE art = :art");
    $stmt->execute(['art' => $codice_articolo]);
    $cmValue = $stmt->fetch(PDO::FETCH_ASSOC);

    // Recupera i dati esistenti dalla tabella "inv_list"
    $stmt = $db->prepare("SELECT qta FROM inv_list WHERE dep = :dep AND art = :art");
    $stmt->execute(['dep' => $deposito, 'art' => $codice_articolo]);
    $existingData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingData) {
        $existingQta = floatval(str_replace(',', '.', $existingData['qta'])); // Sostituisci la virgola con il punto
        $newQta = floatval(str_replace(',', '.', $_POST['qta'])); // Sostituisci la virgola con il punto

        $totalQta = $existingQta + $newQta;
        $totalQta = number_format($totalQta, 2, ',', ''); // Utilizza la virgola come separatore

        // Aggiorna la quantità nel database
        $stmt = $db->prepare("UPDATE inv_list SET qta = :qta WHERE dep = :dep AND art = :art");
        $stmt->execute(['qta' => $totalQta, 'dep' => $deposito, 'art' => $codice_articolo]);

        // Restituisci la risposta JSON
        echo json_encode([
            'status' => 'warning',
            'message' => 'Articolo <b>' . $_POST['codice_articolo'] . '</b> già presente, quantità sommata. Vecchia Qtà <b>' . $existingQta . '</b> + Nuova Qtà <b>' . $newQta . '</b> - TOTALE = <b>' . $totalQta . '</b>'
        ]);
    } else {
        // L'articolo non esiste ancora nel deposito, inseriscilo normalmente
        $data = [
            'dep' => $deposito,
            'art' => $codice_articolo,
            'des' => $selected_des,
            'qta' => $isNumerata ? array_sum($qta) : $qta,
            'num' => $num,
            'is_num' => $valueNumerata,
            'cm' => $cmValue['cm'] ?? null,
        ];

        $sql = "INSERT INTO inv_list (dep, art, des, qta, num, is_num, cm) VALUES (:dep, :art, :des, :qta, :num, :is_num, :cm)";
        $stmt = $db->prepare($sql);
        $insert = $stmt->execute($data);

        if ($insert) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Articolo <b>' . $codice_articolo . '</b> inserito con successo.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Errore durante l\'inserimento del record.',
                'error' => $stmt->errorInfo()
            ]);
        }
    }
} catch (Exception $e) {
    // Stampa l'errore nel log o nella risposta JSON
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    ob_end_clean();
    die();
}

die();
?>
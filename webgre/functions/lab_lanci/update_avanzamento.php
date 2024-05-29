<?php
// Includi il file di configurazione del database o qualsiasi altra operazione necessaria
require_once '../../config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = $_POST['id']; // Ottieni l'ID dall'input POST
    $avanzamentoType = $_POST['avanzamentoType']; // Ottieni il tipo di avanzamento dall'input POST

    // Esegui l'aggiornamento nel database
    $db = getDbInstance(); // Sostituisci con la tua istanza del database
    if ($avanzamentoType === "taglio") {
        $data = array('taglio' => 1, 'preparazione' => 0, 'orlatura' => 0, 'spedizione' => 0, 'avanzamento' => strtoupper($avanzamentoType));
    }
    if ($avanzamentoType === "preparazione") {
        $data = array('taglio' => 1, 'preparazione' => 1, 'orlatura' => 0, 'spedizione' => 0, 'avanzamento' => strtoupper($avanzamentoType));
    }
    if ($avanzamentoType === "orlatura") {
        $data = array('taglio' => 1, 'preparazione' => 1, 'orlatura' => 1, 'spedizione' => 0, 'avanzamento' => strtoupper($avanzamentoType));
    }
    if ($avanzamentoType === "spedizione") {
        $data = array('taglio' => 1, 'preparazione' => 1, 'orlatura' => 1, 'spedizione' => 1, 'avanzamento' => strtoupper($avanzamentoType));
    }

    // $data = array($avanzamentoType => 1, 'avanzamento' => strtoupper($avanzamentoType));

    // Usa l'ID ottenuto dall'input POST per il criterio di aggiornamento
    $db->where('ID', $itemId);

    $update = $db->update('lanci', $data);

    if ($update) {
        echo 'Aggiornamento riuscito';
        $_SESSION['success'] = "Avazanamento eseguito.";
    } else {
        echo 'Errore nell\'aggiornamento: ' . $db->getLastError();
    }
} else {
    echo 'Metodo di richiesta non valido';
}
?>
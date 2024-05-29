<?php
require_once '../../config/config.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idModello = $_POST["id_modello"];
    $codVariante = $_POST["cod_variante"];
    $descVariante = $_POST["desc_variante"];

    // Inizializza l'oggetto mysqliDb
    $db = getDbInstance();

    // Inserisci il record nella tabella var_modelli
    $data = array(
        'id_modello' => $idModello,
        'cod_variante' => $codVariante,
        'desc_variante' => $descVariante
    );

    $db->insert('var_modelli', $data);
    print_r($db->getLastQuery());

    if ($db->getLastErrno() == 0) {
        // L'inserimento è avvenuto con successo
        echo "Record inserito con successo";
    } else {
        // Si è verificato un errore durante l'inserimento
        echo "Errore durante l'inserimento del record: " . $db->getLastError();
    }
}
?>
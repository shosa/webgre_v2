<?php

// Includi il file di connessione al database o qualsiasi altra dipendenza necessaria


// Funzione per inserire un record nel log delle attività e stampare un log in console
function logActivity($user_id, $category, $activity_type, $description, $note = '', $text_query = '')
{
    // Prepara la query SQL per l'inserimento del record nel log delle attività
    $db = getDbInstance();
    $sql = "INSERT INTO activity_log (user_id, category, activity_type, description, note, text_query) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);

    // Esegui la query con i parametri forniti
    $stmt->execute([$user_id, $category, $activity_type, $description, $note, $text_query]);

    // Aggiungi un log in console
    echo "<script>console.log('Evento di tipo $activity_type registrato nel log delle attività. Dettagli: $description');</script>";
}
function replacePlaceholders($pdo, $query, $params)
{
    foreach ($params as $key => $value) {
        $query = str_replace(":$key", $pdo->quote($value), $query);
    }
    return $query;
}
?>
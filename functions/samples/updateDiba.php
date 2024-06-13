<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
session_start();
require_once "../../config/config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $modelId = $_POST['model_id'];
    $descrizioni = isset($_POST['descrizione']) ? $_POST['descrizione'] : [];
    $note = isset($_POST['note']) ? $_POST['note'] : [];
    $unita_misura = isset($_POST['unita_misura']) ? $_POST['unita_misura'] : [];
    $consumo = isset($_POST['consumo']) ? $_POST['consumo'] : [];
    $posizioni = isset($_POST['posizione']) ? $_POST['posizione'] : [];

    $pdo = getDbInstance();
    $pdo->beginTransaction();

    try {
        // Verifica se non sono state inviate righe e cancella tutte le righe esistenti per il modello
        if (empty(array_filter($descrizioni, 'strlen'))) {
            $stmt = $pdo->prepare("DELETE FROM samples_diba WHERE modello_id = :model_id");
            $stmt->execute(['model_id' => $modelId]);
            $stmt = $pdo->prepare("UPDATE samples_modelli SET notify_edits = 1 WHERE id = :modelId");
            $stmt->bindParam(':modelId', $modelId);
            $stmt->execute();
        } else {
            // Recupera le voci esistenti della DiBa per il modello corrente
            $stmt = $pdo->prepare("SELECT * FROM samples_diba WHERE modello_id = :model_id");
            $stmt->execute(['model_id' => $modelId]);
            $existingEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Loop sui dati inviati dal modulo
            foreach ($descrizioni as $key => $descrizione) {
                // Controlla se il campo "Descrizione" è vuoto
                if (!empty($descrizione)) {
                    $existingEntry = findExistingEntryByDescrizione($existingEntries, $descrizione);
                    if ($existingEntry) {
                        // Aggiorna la riga esistente
                        $stmt = $pdo->prepare("UPDATE samples_diba SET descrizione = :descrizione, note = :note, unita_misura = :unita_misura, consumo = :consumo, posizione = :posizione WHERE id = :id");
                        $stmt->bindParam(':descrizione', $descrizione);
                        $stmt->bindParam(':note', $note[$key]);
                        $stmt->bindParam(':unita_misura', $unita_misura[$key]);
                        $stmt->bindParam(':consumo', $consumo[$key]);
                        $stmt->bindParam(':posizione', $posizioni[$key]);
                        $stmt->bindParam(':id', $existingEntry['id']);
                        $stmt->execute();
                        $stmt = $pdo->prepare("UPDATE samples_modelli SET notify_edits = 1 WHERE id = :modelId");
                        $stmt->bindParam(':modelId', $modelId);
                        $stmt->execute();
                    } else {
                        // Inserisce una nuova riga
                        $stmt = $pdo->prepare("INSERT INTO samples_diba (modello_id, descrizione, note, unita_misura, consumo, posizione) VALUES (:modello_id, :descrizione, :note, :unita_misura, :consumo, :posizione)");
                        $stmt->bindParam(':modello_id', $modelId);
                        $stmt->bindParam(':descrizione', $descrizione);
                        $stmt->bindParam(':note', $note[$key]);
                        $stmt->bindParam(':unita_misura', $unita_misura[$key]);
                        $stmt->bindParam(':consumo', $consumo[$key]);
                        $stmt->bindParam(':posizione', $posizioni[$key]);
                        $stmt->execute();
                        $stmt = $pdo->prepare("UPDATE samples_modelli SET notify_edits = 1 WHERE id = :modelId");
                        $stmt->bindParam(':modelId', $modelId);
                        $stmt->execute();
                    }
                }
            }

            // Rimuove le righe esistenti non più presenti nel modulo inviato
            foreach ($existingEntries as $entry) {
                if (!in_array($entry['id'], $_POST['entry_id'])) {
                    $stmt = $pdo->prepare("DELETE FROM samples_diba WHERE id = :id");
                    $stmt->execute(['id' => $entry['id']]);
                    $stmt = $pdo->prepare("UPDATE samples_modelli SET notify_edits = 1 WHERE id = :modelId");
                    $stmt->bindParam(':modelId', $modelId);
                    $stmt->execute();
                }
            }
        }

        // Conferma la transazione
        $pdo->commit();
        $_SESSION['success'] = "DiBa aggiornata con successo!";
    } catch (Exception $e) {
        // Annulla la transazione in caso di errore
        $pdo->rollBack();
        $_SESSION['error'] = "Errore nell'aggiornamento della DiBa: " . $e->getMessage();
    }

    header('Location: editDiba.php?model_id=' . $modelId);
    exit();
}

/**
 * Trova una voce esistente in base alla descrizione.
 *
 * @param array $entries
 * @param string $descrizione
 * @return array|null
 */
function findExistingEntryByDescrizione($entries, $descrizione)
{
    foreach ($entries as $entry) {
        if ($entry['descrizione'] === $descrizione) {
            return $entry;
        }
    }
    return null;
}
?>
<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Recupera il progressivo dalla richiesta GET
$progressivo = $_POST['progressivo'];

// Recupera l'istanza del database
$db = getDbInstance();

// Ottieni tutti i codici articolo unici escludendo il progressivo dato
$other_codes = $db->rawQuery('SELECT DISTINCT codice_articolo FROM exp_dati_articoli WHERE id_documento = ?', array($progressivo));

// Per ogni codice articolo trovato
foreach ($other_codes as $code) {
    // Ottieni l'articolo con lo stesso codice articolo ma con un diverso id_documento
    $db->where('codice_articolo', $code['codice_articolo']);
    $db->where('id_documento', $progressivo, '!='); // Escludi il progressivo corrente
    $article = $db->getOne('exp_dati_articoli', ['voce_doganale', 'prezzo_unitario']);

    // Se è stato trovato un articolo
    if ($article) {
        // Aggiorna l'articolo corrente con i valori trovati
        $data = array(
            'voce_doganale' => $article['voce_doganale'],
            'prezzo_unitario' => $article['prezzo_unitario']
        );
        $db->where('codice_articolo', $code['codice_articolo']);
        $db->where('id_documento', $progressivo);
        $db->update('exp_dati_articoli', $data);
    }
}
// Aggiorna il campo first_boot nella tabella exp_documenti
$data = array(
    'first_boot' => 0
);
$db->where('id', $progressivo);
$db->update('exp_documenti', $data);
?>
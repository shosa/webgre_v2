<?php
require_once '../../config/config.php';

// Ottieni l'istanza di mysqlidb
$db = getDbInstance();

// Query per ottenere la percentuale di scarto per articolo
$db->groupBy('r.articolo');
$db->join('cq_records r', 'p.cartellino = r.cartellino', 'LEFT');
$db->where('r.esito', 'X', '!=');
$scartoPerArticolo = $db->getValue('cq_records p', 'COUNT(r.pa) / SUM(p.pa) * 100');
$db->getLastQuery();

// Query per ottenere la percentuale di scarto totale paia
$db->where('esito', 'X');
$scartoTotalePaia = $db->getValue('cq_records', 'COUNT(*) / SUM(pa) * 100');

// Prepara i dati da restituire come JSON
$response = [
    'labels' => [], // Etichette degli articoli
    'scartoPerArticolo' => [], // Percentuale di scarto per ogni articolo
    'scartoTotalePaia' => [$scartoTotalePaia, 100 - $scartoTotalePaia] // Percentuale di scarto totale paia
];

// Esegui la query per ottenere gli articoli e le relative percentuali di scarto
$result = $db->get('cq_records');
foreach ($result as $row) {
    $response['labels'][] = $row['articolo'];
    $response['scartoPerArticolo'][] = $scartoPerArticolo;
}

// Restituisci i dati come JSON
header('Content-Type: application/json');
echo json_encode($response);

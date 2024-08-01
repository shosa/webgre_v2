<?php

session_start();

require_once '../../config/config.php';

// Check if user is authenticated
require_once BASE_PATH . '/components/auth_validate.php';

// Include necessary utilities or functions
require_once '../../utils/log_utils.php';

// Check if search query is set
if (isset($_GET['search_query'])) {
    $search_query = $_GET['search_query'];
    $pdo = getDbInstance();

    // Prepare SQL statement to fetch data
    if ($search_query === '*') {
        // Se la ricerca è '*', otteniamo tutti i record
        $sql = "SELECT DISTINCT tl.id ,tl.lot , tl.cartel ,tl.id ,tl.type_id, tl.note, tl.timestamp, dati.`Commessa Cli`, tt.name AS type_name
                FROM track_links tl 
                LEFT JOIN track_types tt ON tl.type_id = tt.id
                LEFT JOIN dati ON dati.Cartel = tl.cartel";
        $stmt = $pdo->query($sql);
    } else {
        // Altrimenti, esegui la query normale con LIKE per ricerca iniziale
        $sql = "SELECT DISTINCT tl.id ,tl.lot , tl.cartel ,tl.id ,tl.type_id, tl.note, tl.timestamp, dati.`Commessa Cli`, tt.name AS type_name 
                FROM track_links tl 
                LEFT JOIN track_types tt ON tl.type_id = tt.id
                LEFT JOIN dati ON dati.Cartel = tl.cartel
                WHERE tl.cartel LIKE :search_queryPrefix
                   OR dati.`Commessa Cli` LIKE :search_queryInfix OR tl.lot LIKE :search_queryInfix";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':search_queryPrefix', $search_query . '%', PDO::PARAM_STR);
        $stmt->bindValue(':search_queryInfix', '%' . $search_query . '%', PDO::PARAM_STR);
        $stmt->execute();
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process and display results as tree view
    if ($results) {
        // Initialize an empty array to store the nested tree structure
        $tree = [];

        // Build the tree structure
        foreach ($results as $result) {
            $tree[$result['cartel']]['cartel'] = $result['cartel'];
            $tree[$result['cartel']]['Commessa Cli'] = $result['Commessa Cli']; // Save Commessa Cli in the tree
            $tree[$result['cartel']]['children'][$result['type_id']]['type_name'] = $result['type_name'];
            $tree[$result['cartel']]['children'][$result['type_id']]['lots'][] = [
                'id' => $result['id'], // Aggiungi l'id del record
                'lot' => $result['lot'],
                'timestamp' => $result['timestamp'] // Aggiungiamo il timestamp al lotto
            ];
        }

        // Render the tree structure as HTML
        echo '<ul><h5>Risultati:</h5>';
        foreach ($tree as $cartelino) {
            echo '<li class="text-primary">' . $cartelino['cartel'] . ' (' . $cartelino['Commessa Cli'] . ')'; // Display Commessa Cli
            echo '<ul>';
            foreach ($cartelino['children'] as $type_id => $type) {
                echo '<li class="text-dark"><b>' . $type['type_name'] . '</b>';
              
                echo '<ul>';
                foreach ($type['lots'] as $lot) {
                    echo '<p class="mt-1 border-bottom">' . $lot['lot'] .
                        '<span class="timestamp" style="color:#d1d1d1;">' . $lot['timestamp'] . '</span>' .
                        '<span class="ml-2"><i class="fa fa-pencil edit-lot text-primary" data-id="' . $lot['id'] . '"></i>' .
                        '<i class="fa fa-times ml-2 delete-lot text-danger" data-id="' . $lot['id'] . '"></i></span></li>';
                }
                echo '</ul>';
                echo '</p>';
            }
            echo '</ul>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<div class="alert alert-warning mt-4">Nessun risultato per i dati inseriti.</div>';
    }
} else {
    echo '<div class="alert alert-warning mt-4">Inserisci una query di ricerca.</div>';
}


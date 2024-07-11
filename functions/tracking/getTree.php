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
    $sql = "SELECT tl.*, tt.name AS type_name FROM track_links tl 
            LEFT JOIN track_types tt ON tl.type_id = tt.id
            WHERE tl.cartel = :search_query OR tl.lot = :search_query";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':search_query', $search_query, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process and display results as tree view
    if ($results) {
        // Initialize an empty array to store the nested tree structure
        $tree = [];

        // Build the tree structure
        foreach ($results as $result) {
            $tree[$result['cartel']]['cartel'] = $result['cartel'];
            $tree[$result['cartel']]['children'][$result['type_id']]['type_name'] = $result['type_name'];
            $tree[$result['cartel']]['children'][$result['type_id']]['lots'][] = $result['lot'];
        }

        // Render the tree structure as HTML
        echo '<ul>';
        foreach ($tree as $cartelino) {
            echo '<h5>Cartellino: </h5>';
            echo '<li class="text-primary">' . $cartelino['cartel'];
            echo '<ul>';
            foreach ($cartelino['children'] as $type_id => $type) {
                echo '<li class="text-dark" ><b>' . $type['type_name'] . '</b>';
                echo '<ul >';
                foreach ($type['lots'] as $lot) {
                    echo '<i>#: ' . $lot . '</i></br>';
                }
                echo '</ul>';
                echo '</li>';
            }
            echo '</ul>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<div class="alert alert-warning mt-4">Nessun risultato per i dati inseriti.</div>';
    }
}
?>
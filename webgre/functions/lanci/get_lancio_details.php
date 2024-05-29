<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Assicurati di avere l'ID del lancio passato come parametro GET
if (isset($_GET['lancioID'])) {
    $lancioID = $_GET['lancioID'];

    // Effettua una query per ottenere i dettagli del lancio unendo le tabelle
    $db = getDbInstance();
    $db->join('basi_modelli', 'lanci.id_modello = basi_modelli.ID', 'LEFT');
    $db->join('var_modelli', 'lanci.id_variante = var_modelli.ID', 'LEFT');
    $db->where('lanci.lancio', $lancioID);
    $lancioDetails = $db->get('lanci', null, ['lanci.*', 'basi_modelli.path_to_image', 'var_modelli.nome_completo']);
    if ($lancioDetails) {
        // Formatta i dettagli come una tabella HTML
        $html = '<h4> <b>#' . htmlspecialchars($lancioDetails[0]['lancio']) . '</b></h4>';
        $html .= '<div class="table-responsive">';
        $html .= '<table class="table table-striped table-bordered table-condensed">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th width="5%">IMG</th>';
        $html .= '<th width="80%">ARTICOLO</th>';
        $html .= '<th width="15%">PAIA</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody';

        foreach ($lancioDetails as $lancio) {
            $imagePath = (isset($lancio['path_to_image']) && !empty($lancio['path_to_image'])) ? $lancio['path_to_image'] : 'src/img/default.jpg';

            $html .= '<tr>';
            $html .= '<td><img src="../../' . htmlspecialchars($imagePath) . '" alt="Immagine" style="max-width: 80px; max-height: 80px; border: 1px solid lightgrey;"></td>';
            $html .= '<td style="vertical-align:middle;">' . (isset($lancio['nome_completo']) ? htmlspecialchars($lancio['nome_completo']) : '') . '</td>';
            $html .= '<td style="vertical-align:middle;">' . (isset($lancio['paia']) ? htmlspecialchars($lancio['paia']) : '') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
    } else {
        $html = 'Nessun dettaglio trovato per l\'ID Lancio specificato.';
    }

    // Restituisci il risultato come risposta AJAX
    echo $html;
} else {
    // Gestisci l'errore se l'ID del lancio non è stato fornito correttamente
    echo 'Errore: ID Lancio non valido.';
}
?>
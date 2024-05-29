<?php
require_once '../../config/config.php';
$db = getDbInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $recordId = filter_input(INPUT_GET, 'id', FILTER_UNSAFE_RAW);

    $ddt = $db->where('id', $recordId)->getOne('exp_documenti');
    $terzista = $db->where('id', $ddt['id_terzista'])->getOne('exp_terzisti', ['ragione_sociale', 'nazione']);
    
    $lanci = $db->where('id_doc', $recordId)->get('exp_dati_lanci_ddt', null, ['lancio', 'articolo', 'paia', 'note']);

    include BASE_PATH . '/includes/flash_messages.php';

    $totalPaia = 0;  // Variabile per accumulare il totale delle paia
    
    
    echo '<p><strong>Numero Documento:</strong> ' . $ddt['id'] . '</p>';
    echo '<p><strong>Destinatario:</strong> ' . xss_clean($terzista['ragione_sociale']) . ' (' . xss_clean($terzista['nazione']) . ')</p>';
    echo '<p><strong>Data:</strong> ' . xss_clean($ddt['data']) . '</p>';
    echo '<p><strong>Stato:</strong> ' . xss_clean($ddt['stato']) . '</p>';

    echo '<h4>Dettagli Lanci DDT</h4>';
    echo '<table class="table table-bordered">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Lancio</th>';
    echo '<th>Codice Articolo</th>';
    echo '<th>Paia</th>';
    echo '<th>Note</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($lanci as $lancio) {
        $totalPaia += $lancio['paia'];  // Aggiungi il numero di paia al totale
        echo '<tr>';
        echo '<td>' . xss_clean($lancio['lancio']) . '</td>';
        echo '<td>' . xss_clean($lancio['articolo']) . '</td>';
        echo '<td>' . xss_clean($lancio['paia']) . '</td>';
        echo '<td>' . xss_clean($lancio['note']) . '</td>';
        echo '</tr>';
    }
    echo '<tr>';
    echo '<td colspan="2"><strong>Totale Paia</strong></td>';
    echo '<td><strong>' . $totalPaia . '</strong></td>';
    echo '<td></td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
}
?>

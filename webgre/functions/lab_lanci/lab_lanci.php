<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Users class
require_once BASE_PATH . '/lib/Lanci/Lanci.php';
$modelli = new Lanci();


// Get Input data from query string
$search_string = filter_input(INPUT_GET, 'search_string');
$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');
$del_id = filter_input(INPUT_GET, 'del_id');

// Per page limit for pagination.
$pagelimit = 20;

// Get current page.
$page = filter_input(INPUT_GET, 'page');
if (!$page) {
    $page = 1;
}

// If filter types are not selected we show latest added data first
if (!$filter_col) {
    $filter_col = 'lancio';
}
if (!$order_by) {
    $order_by = 'Asc';
}

//Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();
$username = $_SESSION['username'];
$labUserRow = $db->where('user', $username)->getOne('lab_user', 'lab');
$labIDRow = $db->where('Nome', $labUserRow['lab'])->getOne('laboratori', 'ID');
$labValue = $labUserRow['lab'];
$labID = $labIDRow['ID'];
$select = array('lanci.ID', 'lanci.taglio', 'lanci.preparazione', 'lanci.orlatura', 'lanci.spedizione', 'lanci.lancio', 'lanci.avanzamento', 'lanci.id_lab', 'SUM(lanci.paia) AS paia_total', 'lanci.id_modello', 'linee.descrizione', 'lanci.id_variante', 'lanci.id_lab', 'lanci.stato', 'lanci.paia', 'basi_modelli.descrizione AS modello_descrizione', 'var_modelli.desc_variante AS variante_descrizione');
$db->groupBy('lanci.lancio');
$db->join('basi_modelli', 'lanci.id_modello = basi_modelli.ID', 'LEFT');
$db->join('linee', 'lanci.linea = linee.sigla', 'LEFT');
$db->join('var_modelli', 'lanci.id_variante = var_modelli.ID', 'LEFT');
$db->where('id_lab', $labID);

//Start building query according to input parameters.
// If search string
if ($search_string) {
    $db->where('lancio', '%' . $search_string . '%', 'like');
}

//If order by option selected
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

// Set pagination limit
$db->pageLimit = $pagelimit;

// Get result of the query.
$rows = $db->arraybuilder()->paginate('lanci', $page, $select);
$total_pages = $db->totalPages;

include BASE_PATH . '/includes/header.php';
?>
<style>
    .stato.attesa {
        color: red;
        font-weight: bold;
        animation: blink 1s linear infinite;
    }

    @keyframes blink {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0;
        }

        100% {
            opacity: 1;
        }
    }
</style>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Lanci in lavoro presso
                <b>
                    <?php echo $labValue; ?>
                </b>
            </h1>
        </div>
    </div>
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>

    <?php
    if (isset($del_stat) && $del_stat == 1) {
        echo '<div class="alert alert-info">Eliminato correttamente</div>';
    }
    ?>

    <!-- Filters -->

    <!-- //Filters -->

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th width="5%">Lancio</th>
                    <th width="20%">Linea</th>
                    <th width="5%">Paia</th>
                    <th width="20%">% Fasi</th>
                    <th width="20%">Stato</th>
                    <th width="5%">Azioni</th>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row):
                    if ($row['stato'] != 'IN ATTESA'): ?>
                        <tr>
                            <td style="vertical-align: middle; text-align:center;"><b>
                                    <?php echo htmlspecialchars($row['lancio']); ?>
                                </b>
                            </td>
                            <!-- Mostra la concatenazione delle descrizioni dei modelli e varianti -->
                            <td style="vertical-align: middle;">
                                <?php echo htmlspecialchars($row['descrizione']); ?>
                            </td>
                            <td style="vertical-align: middle;">
                                <?php echo htmlspecialchars($row['paia_total']); ?>
                            </td>
                            <td style="padding-top:30px;">
                                <?php
                                $lancio = $row['lancio'];

                                // Calcola il totale delle paia totali moltiplicate per 4
                                $totalPaia = $row['paia_total'] * 4;

                                // Inizializza il conteggio delle paia per le fasi completate
                                $completedPaia = 0;

                                // Ricalcola la query per ottenere il totale delle paia per ciascuna fase
                                $totalTaglio = $db->where('lancio', $lancio)->where('taglio', 1)->getValue('lanci', 'SUM(paia)');
                                $totalPreparazione = $db->where('lancio', $lancio)->where('preparazione', 1)->getValue('lanci', 'SUM(paia)');
                                $totalOrlatura = $db->where('lancio', $lancio)->where('orlatura', 1)->getValue('lanci', 'SUM(paia)');
                                $totalSpedizione = $db->where('lancio', $lancio)->where('spedizione', 1)->getValue('lanci', 'SUM(paia)');

                                // Aggiungi le paia di ciascuna fase completata
                                $completedPaia += $totalTaglio + $totalPreparazione + $totalOrlatura + $totalSpedizione;

                                // Calcola la percentuale di completamento
                                $progressValue = ($totalPaia > 0) ? (($completedPaia * 100) / $totalPaia) : 0;

                                // Arrotonda la percentuale a 2 cifre decimali
                                $progressValue = round($progressValue, 2);

                                // Visualizza la percentuale come una progress bar
                                echo '<div class="progress" style="border:solid 1pt black">';
                                echo '<div class="progress-bar progress-bar-info" role="progressbar" style="min-width: 5%; width: ' . $progressValue . '%;" aria-valuenow="' . $progressValue . '" aria-valuemin="0" aria-valuemax="100">';
                                echo $progressValue . '%';
                                echo '</div>';
                                echo '</div>';
                                ?>
                            </td>

                            </td>
                            <td style="vertical-align: middle;">
                                <?php echo htmlspecialchars($row['stato']); ?>
                            </td>
                            <td style="vertical-align: middle; text-align:center;">
                                <a href="open_lab_lanci.php?lancio=<?php echo $row['lancio']; ?>&operation=plus"
                                    class="btn btn-warning"><i class="fa fa-folder-open"></i></a>

                            </td>
                            <input type="hidden" name="hidden_id" value="<?php echo htmlspecialchars($row['ID']); ?>">
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- //Table -->

    <!-- Pagination -->
    <div class="text-center">
        <?php
        if (!empty($_GET)) {
            // We must unset $_GET[page] if previously built by http_build_query function
            unset($_GET['page']);
            // To keep the query sting parameters intact while navigating to next/prev page,
            $http_query = "?" . http_build_query($_GET);
        } else {
            $http_query = "?";
        }
        // Show pagination links
        if ($total_pages > 1) {
            echo '<ul class="pagination text-center">';
            for ($i = 1; $i <= $total_pages; $i++) {
                ($page == $i) ? $li_class = ' class="active"' : $li_class = '';
                echo '<li' . $li_class . '><a href="lab_lanci.php' . $http_query . '&page=' . $i . '">' . $i . '</a></li>';
            }
            echo '</ul>';
        }
        ?>
    </div>
    <!-- //Pagination -->
</div>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>
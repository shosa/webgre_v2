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
$select = array('lanci.ID', 'lanci.lancio', 'lanci.avanzamento', 'lanci.id_lab', 'SUM(lanci.paia) AS paia_total', 'lanci.id_modello', 'linee.descrizione', 'lanci.id_variante', 'lanci.id_lab', 'lanci.stato', 'lanci.paia', 'basi_modelli.descrizione AS modello_descrizione', 'var_modelli.desc_variante AS variante_descrizione');
$db->groupBy('lanci.lancio');
$db->join('basi_modelli', 'lanci.id_modello = basi_modelli.ID', 'LEFT');
$db->join('linee', 'lanci.linea = linee.sigla', 'LEFT');
$db->join('var_modelli', 'lanci.id_variante = var_modelli.ID', 'LEFT');


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
            <h1 class="page-header page-action-links text-left">Lanci in preparazione
                <b>

                </b>
            </h1>
        </div>
    </div>
    <hr>
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>

    <?php
    if (isset($del_stat) && $del_stat == 1) {
        echo '<div class="alert alert-info">Eliminato correttamente</div>';
    }
    ?>

    <!-- Filters -->
    <div class="well text-center filter-form">
        <b>Qui sono riportati, raggruppati per linea di produzione, i lanci in fase di preparazione,
            fino alla spedizione il laboratorio scelto. </b>
    </div>
    <hr>
    <!-- //Filters -->

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th width="10%">Lancio</th>
                    <th width="40%">Linea <a style="float:right; color:black;">
                            <i class="fal fa-hand-pointer fa-flip-both"></i>
                        </a></th>
                    <th width="10%">Paia</th>
                    <th width="30%">Laboratorio scelto</th>
                    <th width="10%">Stato</th>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row):
                    if ($row['stato'] === 'IN ATTESA'): ?>
                        <tr>
                            <td style="vertical-align: middle;">
                                <?php echo htmlspecialchars($row['lancio']); ?>
                            </td>
                            <!-- Mostra la concatenazione delle descrizioni dei modelli e varianti -->
                            <td style="vertical-align: middle;">
                                <?php echo htmlspecialchars($row['descrizione']); ?>
                                <a href="#" style="float:right;" class="open-lanci-modal"
                                    data-lancio-id="<?php echo htmlspecialchars($row['lancio']); ?>">
                                    <i class="fad fa-search"
                                        style="--fa-primary-color: #c1ab7b; --fa-secondary-color: #3d94d6;"></i>
                                </a>
                            </td>
                            <td style="vertical-align: middle;">
                                <?php echo htmlspecialchars($row['paia_total']); ?>
                            </td>
                            <td>
                                <?php
                                if ($row['id_lab'] === null) {
                                    echo '<span class="stato attesa">DA ASSEGNARE</span>';
                                } else {
                                    $select2 = array('Nome');
                                    $db->where('ID', htmlspecialchars($row['id_lab']));
                                    $rows2 = $db->arraybuilder()->paginate('laboratori', $page, $select2);
                                    echo htmlspecialchars($rows2[0]['Nome']);
                                }
                                ?>
                            </td>
                            <td style="vertical-align: middle;">
                                <span class="stato <?php echo ($row['stato'] === 'IN ATTESA') ? 'attesa' : ''; ?>">
                                    <b>
                                        IN PREPARAZIONE
                                    </b>
                                </span>
                            </td>
                            <input type="hidden" name="hidden_id" value="<?php echo htmlspecialchars($row['ID']); ?>">
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="modal fade" id="lanciModal" tabindex="-1" role="dialog" aria-labelledby="lanciModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="lanciModalLabel">Dettaglio Lancio</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Qui verrà visualizzato il dettaglio del lancio -->
                        <div id="lanciModalContent"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                    </div>
                </div>
            </div>
        </div>
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
                echo '<li' . $li_class . '><a href="lab_wait_lanci.php' . $http_query . '&page=' . $i . '">' . $i . '</a></li>';
            }
            echo '</ul>';
        }
        ?>
    </div>
    <!-- //Pagination -->
</div>
<script>

    $(document).ready(function () {
        // Gestisci il clic sull'icona di lente d'ingrandimento
        $(".open-lanci-modal").click(function () {
            var lancioID = $(this).data('lancio-id');

            // Effettua una richiesta AJAX per ottenere i dettagli del lancio
            $.ajax({
                url: 'get_lancio_details.php', // Sostituisci con l'URL del file che restituirà i dettagli del lancio
                method: 'GET',
                data: { lancioID: lancioID },
                success: function (data) {
                    // Inserisci i dettagli nel contenuto del modale
                    $('#lanciModalContent').html(data);
                    // Apri il modale
                    $('#lanciModal').modal('show');
                }
            });
        });
    });

</script>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>
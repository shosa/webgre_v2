<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Costumers class
require_once BASE_PATH . '/lib/Inventario/Inventario.php';
$inventario = new Inventario();


// Get Input data from query string
$search_string = filter_input(INPUT_GET, 'search_string');
$search_cm = filter_input(INPUT_GET, 'search_cm');

include BASE_PATH . '/includes/header-nomenu.php';
// Per page limit for pagination.
$pagelimit = 15;

// Get current page.
$page = filter_input(INPUT_GET, 'page');
if (!$page) {
    $page = 1;
}

// Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();
$select = array('ID', 'dep', 'cm', 'art', 'des', 'qta', 'num', 'is_num');

// Start building query according to input parameters.
// If search string
if ($search_string) {
    $db->where('dep', $_SESSION['deposito_selezionato']);
    $db->where('des', '%' . $search_string . '%', 'like');
    $db->orwhere('art', '%' . $search_string . '%', 'like');
}
if ($search_cm) {
    $db->where('dep', $_SESSION['deposito_selezionato']);
    $db->where('cm', '%' . $search_cm . '%', 'like');
}

// Set pagination limit
$db->pageLimit = $pagelimit;

// Get result of the query.
$rows = $db->arraybuilder()->where('dep', $_SESSION['deposito_selezionato'])->paginate('inv_list', $page, $select);
$total_pages = $db->totalPages;
$db->where('dep', $_SESSION['deposito_selezionato']);
$db->groupBy('cm');
$uniqueCmValues = $db->get('inv_list', null, 'cm');

$db->where('dep', $_SESSION['deposito_selezionato']);
$deposito = $db->getOne('inv_depositi', null, ['dep', 'des']);
?>
<!-- Main container -->

<div id="page-wrapper">

    <div class="row align-items-center">
        <div class="col-lg-6">
            <div class="page-action-links text-left">
                <form action="inv_inventory.php" method="post">
                    <input type="hidden" name="select_deposito"
                        value="<?php echo htmlspecialchars($_SESSION['deposito_selezionato']); ?>">
                    <button type="submit" class="btn btn-warning" style="font-size:20pt;">
                        <i class="fad fa-chevron-double-left"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="col-lg-6">
            <h2 class="page-header page-action-links text-right">Elenco Inventario
                <span style="color:White;background-color:#198754;padding:7px;border-radius:10px;">
                    <?php echo $deposito['des'] ?>
                </span>
            </h2>
        </div>

    </div>

    <hr>
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>

    <!-- Filters -->
    <div class="well text-center filter-form" style="background-color:#f2f2f2;padding:1%;border-radius:20px;">
        <form class="form form-inline" action="">
            <label for="input_search_idrip" style="margin-right:1%">Materiale:</label>
            <input type="text" class="form-control" id="input_search" name="search_string" value=""
                placeholder="Descrizione articolo" style="margin-right:1%">
            <button type="submit" class="btn btn-primary" style="background-color:#6610f2;border-color:#6610f2;">
                CERCA <i class="fad fa-sliders-h"></i>
            </button>

        </form>
    </div>
    <hr>
    <!-- //Filters -->

    <div id="export-section">
        <a href="export_riparazioni.php"><button class="btn btn-sm btn-primary">ESPORTA VISTA IN EXCEL <i
                    class="fad fa-file-excel"></i></button></a>
    </div>
    <div class="text-center" style="margin-bottom: 10px;">
        <?php foreach ($uniqueCmValues as $cmValue): ?>
            <a href="#" class="btn btn-info btn-sm" onclick="filterByCm('<?php echo $cmValue['cm']; ?>')">
                <?php echo $cmValue['cm']; ?>
            </a>
        <?php endforeach; ?>
    </div>
    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th style="text-align:center;" width="5%">ID</th>
                    <th style="text-align:center;" width="5%">DEP</th>
                    <th width="10%">CM</th>
                    <th width="15%">ARTICOLO</th>
                    <th style="text-align:center;" width="50%">DESCRIZIONE</th>
                    <th width="10%">QTA</th>
                    <th style="text-align:center;" width="10%">AZIONI</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>

                        <td style="vertical-align: middle; text-align:center;" data-id="<?php echo $row['ID']; ?>">
                            <?php echo $row['ID']; ?>
                        </td>
                        <td style="vertical-align: middle; text-align:center;" data-id="<?php echo $row['dep']; ?>">
                            <?php echo $row['dep']; ?>
                        </td>
                        <td style="vertical-align: middle; " data-id="<?php echo $row['cm']; ?>">
                            <?php echo xss_clean($row['cm']); ?>
                        </td>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['art']; ?>">
                            <?php echo xss_clean($row['art']); ?>
                        </td>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['des']; ?>">
                            <?php echo xss_clean($row['des']); ?>
                        </td>
                        <td style="vertical-align: middle;text-align:center;" data-id="<?php echo $row['qta']; ?>">
                            <?php
                            if ($row['is_num'] == 1) {
                                // Calcola la somma dei valori in 'num'
                                $numValues = explode(';', $row['num']);
                                $qtaValues = explode(';', $row['qta']);
                                $sumNum = array_sum(array_map('intval', $qtaValues));
                                echo $sumNum . '&ensp;&ensp;'; // Mostra la somma
                                // Mostra l'icona della lente di ingrandimento
                                echo '<i class="fas fa-search" style="cursor: pointer; color:#6610f2; background-color:#ededed; border-radius: 10px; padding:7px;" onclick="openDetailsModal(' . htmlspecialchars(json_encode($numValues, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS), ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars(json_encode(explode(';', $row['qta']), JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS), ENT_QUOTES, 'UTF-8') . ')"></i>';

                            } else {
                                // Altrimenti, mostra la quantità
                                echo xss_clean($row['qta']);
                            }
                            ?>
                        </td>
                        <input type="hidden" data-id="<?php echo $row['num']; ?>">
                        <input type="hidden" data-id="<?php echo $row['is_num']; ?>">
                        <td style="vertical-align: middle; text-align:center;">
                            <div class="btn-group">
                                <a href="inv_edit_item.php?inventario_id=<?php echo $row['ID']; ?>&operation=edit"
                                    class="btn btn-primary">
                                    <i class="far fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-danger delete_btn" data-toggle="modal"
                                    data-target="#confirm-delete-<?php echo $row['ID']; ?>">
                                    <i class="fal fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" style="z-index: 5000" id="confirm-delete-<?php echo $row['ID']; ?>"
                        role="dialog" aria-labelledby="confirm-delete-modal-label" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="inv_delete_item.php" method="POST">
                                <!-- Modal content -->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirm-delete-modal-label">Conferma</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body" style="color: #f96363; background: #ffe9e9;">
                                        <input type="hidden" name="del_id" id="del_id" value="<?php echo $row['ID']; ?>">
                                        <p>Sicuro di voler procedere ad eliminare questa riga?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-danger">Si</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- //Delete Confirmation Modal -->
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- //Table -->
    <!-- MODALE TAGLIE -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Dettaglio Taglie</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive" style="width: 100%;">
                        <table class="table table-bordered table-condensed">
                            <thead>
                                <tr id="numRow">
                                    <!-- Inserisci qui le colonne per i valori di num -->
                                </tr>
                                <tr id="qtaRow">
                                    <!-- Inserisci qui le colonne per i valori di qta -->
                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
    <!-- // MODALE TAGLIE -->

    <!-- Pagination -->
    <div class="text-center">
        <?php echo paginationLinks($page, $total_pages, 'inv_all_items.php'); ?>
    </div>
    <!-- //Pagination -->
</div>
<script>
    // Funzione per aprire il modal e popolare la tabella con i dati
    function openDetailsModal(numValues, qtaValues) {
        var numRow = document.getElementById('numRow');
        var qtaRow = document.getElementById('qtaRow');

        // Pulisci le righe della tabella
        numRow.innerHTML = '';
        qtaRow.innerHTML = '';

        // Imposta il numero massimo di colonne desiderato (ad esempio, 10 colonne)


        // Aggiungi le colonne per i valori di num (massimo 10 colonne)
        numValues.forEach(function (num) {
            var cell = document.createElement('th');
            cell.textContent = num;
            cell.style.backgroundColor = '#5095fa';
            cell.style.color = 'white';
            cell.style.border = "solid 1pt #0d6efd";
            cell.style.width = "12%";
            numRow.appendChild(cell);
        });

        // Aggiungi le colonne per i valori di qta (massimo 10 colonne)
        qtaValues.forEach(function (qta) {
            var cell = document.createElement('th');
            cell.textContent = qta;
            cell.style.width = "12%";
            cell.style.fontWeight = "normal";
            cell.style.border = "solid 1pt #ededed";
            qtaRow.appendChild(cell);
        });

        // Apri il modal
        $('#detailsModal').modal('show');
    }
    function filterByCm(cm) {
        // Imposta il valore di 'cm' nel parametro della query string 'search_cm'
        var queryString = window.location.search;
        var urlParams = new URLSearchParams(queryString);

        // Imposta il valore di 'cm' nella query string
        urlParams.set('search_cm', cm);

        // Ricostruisci l'URL con la nuova query string
        var newUrl = window.location.pathname + '?' + urlParams.toString();

        // Reindirizza alla nuova URL
        window.location.href = newUrl;
    }
</script>

<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>
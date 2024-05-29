<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Costumers class
require_once BASE_PATH . '/lib/Riparazioni/Riparazioni.php';
$riparazioni = new Riparazioni();
require_once BASE_PATH . '/lib/Numerate/Numerate.php';
$numerate = new Numerate();

// Get Input data from query string
$search_string = filter_input(INPUT_GET, 'search_string');
$search_string_linea = filter_input(INPUT_GET, 'search_string_linea');
$search_string_idrip = filter_input(INPUT_GET, 'search_string_idrip');
$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');


// Per page limit for pagination.
$pagelimit = 15;

// Get current page.
$page = filter_input(INPUT_GET, 'page');
if (!$page) {
    $page = 1;
}

// If filter types are not selected we show latest added data first
if (!$filter_col) {
    $filter_col = 'IDRIP';
}
if (!$order_by) {
    $order_by = 'Desc';
}

// Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();
$select = array('IDRIP', 'CODICE', 'ARTICOLO', 'QTA', 'CARTELLINO', 'COMMESSA', 'UTENTE', 'LABORATORIO', 'URGENZA', 'DATA', 'REPARTO', 'LINEA', 'CAUSALE', 'COMPLETA');

// Start building query according to input parameters.
// If search string
if ($search_string) {
    $db->where('ARTICOLO', '%' . $search_string . '%', 'like');
    $db->orwhere('CARTELLINO', '%' . $search_string . '%', 'like');
}

if ($search_string_linea) {
    $db->where('LINEA', '%' . $search_string_linea . '%', 'like');
}
if ($search_string_idrip) {
    $db->where('IDRIP', '%' . $search_string_idrip . '%', 'like');
}

// If order by option selected
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

// Set pagination limit
$db->pageLimit = $pagelimit;

// Get result of the query.
$rows = $db->arraybuilder()->paginate('riparazioni', $page, $select);
$total_pages = $db->totalPages;

include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header page-action-links text-left">Riparazioni</h1>
        </div>

        <div class="col-lg-6">
            <div class="page-action-links text-right">
                <a href="add_step1.php?operation=create" class="btn btn-info" style="font-size:18pt;"><i
                        class="fas fa-plus"></i></a>
            </div>
        </div>
    </div>
    <hr>
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>

    <!-- Filters -->
    <div class="well text-center filter-form" style="background-color:#f2f2f2;padding:1%;border-radius:20px;">
        <form class="form form-inline" action="">
            <label for="input_search_idrip" style="margin-right:1%">Cerca:</label>
            <input type="number" class="form-control" id="input_search_idrip" name="search_string_idrip"
                value="<?php echo $search_string_idrip ? htmlspecialchars($search_string_idrip) : ''; ?>"
                placeholder="IDRIP" style="margin-right:1%">
            <input type="text" class="form-control" id="input_search" name="search_string"
                value="<?php echo $search_string ? htmlspecialchars($search_string) : ''; ?>"
                placeholder="Descrizione articolo" style="margin-right:1%">
            <input type="text" class="form-control" id="input_search_linea" name="search_string_linea"
                value="<?php echo $search_string_linea ? htmlspecialchars($search_string_linea) : ''; ?>"
                placeholder="Linea" style="margin-right:1%">
            <label for="input_order" style="margin-right:1%">Ordina: </label>
            <select name="filter_col" class="form-control" style="margin-right:1%">
                <?php
                foreach ($riparazioni->setOrderingValues() as $opt_value => $opt_name) {
                    // Check if $opt_value starts with 'P' followed by a number from 01 to 20
                    if (!preg_match('/^P\d{2}$/', $opt_value)) {
                        // If it doesn't start with 'P' followed by a number from 01 to 20, create the option
                        ($order_by === $opt_value) ? $selected = 'selected' : $selected = '';
                        echo ' <option value="' . $opt_value . '" ' . $selected . '>' . $opt_name . '</option>';
                    }
                }

                ?>
            </select>
            <select name="order_by" class="form-control" id="input_order" style="margin-right:1%">
                <option value="Asc" <?php
                if ($order_by == 'Asc') {
                    echo 'selected';
                }
                ?>>Asc</option>
                <option value="Desc" <?php
                if ($order_by == 'Desc') {
                    echo 'selected';
                }
                ?>>Desc</option>
            </select>
            <button type="submit" class="btn btn-primary" style="background-color:#6610f2;border-color:#6610f2;">
                APPLICA FILTRI <i class="fad fa-sliders-h"></i>
            </button>

        </form>
    </div>
    <hr>
    <!-- //Filters -->

    <div id="export-section">
        <a href="export_riparazioni.php"><button class="btn btn-sm btn-primary">ESPORTA VISTA IN EXCEL <i
                    class="fad fa-file-excel"></i></button></a>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th style="text-align:center;" width="0.5%"></th>
                    <th style="text-align:center;" width="3%">ID</th>
                    <th width="13%">CODICE</th>
                    <th width="27%">ARTICOLO</th>
                    <th style="text-align:center;" width="3%">QTA</th>
                    <th width="13%">LABORATORIO</th>
                    <th style="text-align:center;" width="8%">CARTELLINO</th>
                    <th style="text-align:center;" width="5%">DATA</th>
                    <th style="text-align:center;" width="8%">REPARTO</th>
                    <th style="text-align:center;" width="5%">LINEA</th>
                    <th style="text-align:center;" width="13%">AZIONI</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr <?php if ($row['COMPLETA'] == 1)
                        echo ' style="background-color: #dbfbdb;"'; ?>>
                        <td
                            style="vertical-align: middle; text-align:center; background-color: <?php echo getUrgencyColor($row['URGENZA']); ?>">

                        </td>

                        <td style="vertical-align: middle; text-align:center;" data-id="<?php echo $row['IDRIP']; ?>">
                            <?php echo $row['IDRIP']; ?>
                        </td>
                        <td style="vertical-align: middle; " data-id="<?php echo $row['CODICE']; ?>">
                            <?php echo xss_clean($row['CODICE']); ?>
                        </td>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['ARTICOLO']; ?>">
                            <?php echo xss_clean($row['ARTICOLO']); ?>
                        </td>
                        <td style="vertical-align: middle; text-align:center;" data-id="<?php echo $row['QTA']; ?>">
                            <?php echo xss_clean($row['QTA']); ?>
                        </td>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['LABORATORIO']; ?>">
                            <?php echo xss_clean($row['LABORATORIO']); ?>
                        </td>
                        <td style="vertical-align: middle; text-align:center;" data-id="<?php echo $row['CARTELLINO']; ?>">
                            <?php echo xss_clean($row['CARTELLINO']); ?>
                        </td>
                        <td style="vertical-align: middle;text-align:center;" data-id="<?php echo $row['DATA']; ?>">
                            <?php echo xss_clean($row['DATA']); ?>
                        </td>
                        <td style="vertical-align: middle;text-align:center;" data-id="<?php echo $row['REPARTO']; ?>">
                            <?php echo xss_clean($row['REPARTO']); ?>
                        </td>
                        <td style="vertical-align: middle;text-align:center;" data-id="<?php echo $row['LINEA']; ?>">
                            <?php echo xss_clean($row['LINEA']); ?>
                        </td>
                        <input type="hidden" data-id="<?php echo $row['CAUSALE']; ?>">
                        <input type="hidden" data-id="<?php echo $row['UTENTE']; ?>">
                        <td style="vertical-align: middle; text-align:center;">
                            <div class="btn-group">
                                <a href="#" class="btn btn-success show-record-details"
                                    data-record-id="<?php echo $row['IDRIP']; ?>">
                                    <i class="fas fa-search"></i>
                                </a>
                                <a href="edit_riparazioni.php?riparazione_id=<?php echo $row['IDRIP']; ?>&operation=edit"
                                    class="btn btn-primary">
                                    <i class="far fa-edit"></i>
                                </a>
                                <div class="btn-group">
                                    <a href="file_preview.php?riparazione_id=<?php echo $row['IDRIP']; ?>" type="button"
                                        class="btn btn-warning">
                                        <i class="far fa-print"></i>
                                    </a>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-warning dropdown-toggle btn-xs"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="caret"></span>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item"
                                                href="download_report.php?riparazione_id=<?php echo $row['IDRIP']; ?>">
                                                <i class="fad fa-download"></i> Scarica
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <a href="#" class="btn btn-danger delete_btn" data-toggle="modal"
                                    data-target="#confirm-delete-<?php echo $row['IDRIP']; ?>">
                                    <i class="fal fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" style="z-index: 5000" id="confirm-delete-<?php echo $row['IDRIP']; ?>"
                        role="dialog" aria-labelledby="confirm-delete-modal-label" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="delete_riparazioni.php" method="POST">
                                <!-- Modal content -->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirm-delete-modal-label">Conferma</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body" style="color: #f96363; background: #ffe9e9;">
                                        <input type="hidden" name="del_id" id="del_id" value="<?php echo $row['IDRIP']; ?>">
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
    <div class="modal fade" id="record-details-modal" tabindex="-1" role="dialog"
        aria-labelledby="record-details-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="record-details-modal-label">Dettagli</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Contenuto del modal per i dettagli del record -->
                    <div id="record-details"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="text-center">
        <?php echo paginationLinks($page, $total_pages, 'riparazioni.php'); ?>
    </div>
    <!-- //Pagination -->
</div>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php';
function getUrgencyColor($urgency)
{
    switch ($urgency) {
        case 'BASSA':
            return '#9dd49d'; // Verde
        case 'MEDIA':
            return '#f6ce95'; // Giallo
        case 'ALTA':
            return '#e89895'; // Rosso
        default:
            return ''; // Colore predefinito o nessun colore
    }
}
?>
<script>
    $(document).ready(function () {
        // Aggiungi un gestore di eventi al clic sul pulsante "fa-search"
        $('.show-record-details').click(function () {
            var recordId = $(this).data('record-id');

            // Esegui una richiesta AJAX per ottenere i dettagli del record dal server

            $.ajax({
                url: 'get_riparazione_details.php',
                type: 'GET',
                data: { id: recordId },
                success: function (response) {
                    // Inserisci i dettagli nel modal
                    $('#record-details').html(response);
                    // Apri il modal
                    $('#record-details-modal').modal('show');
                },
                error: function (xhr, status, error) {
                    // Gestisci eventuali errori
                    console.error(error);
                }
            });

        });
    });

</script>
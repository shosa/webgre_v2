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

//Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();

// Prima query per ottenere il valore di LABORATORIO da lab_user
$username = $_SESSION['username'];
$labUserRow = $db->where('user', $username)->getOne('lab_user', 'lab');
$labValue = $labUserRow['lab'];


if ($search_string) {
    $db->where('ARTICOLO', '%' . $search_string . '%', 'like');
    $db->orwhere('CARTELLINO', '%' . $search_string . '%', 'like');
}

if ($search_string_linea) {
    $db->where('LINEA', '%' . $search_string_linea . '%', 'like');
}

// Applica la condizione "LABORATORIO = $labValue"

$db->where('LABORATORIO', $labValue);

// Esegui la query delle riparazioni con la condizione where applicata
$select = array('IDRIP', 'CODICE', 'LABORATORIO', 'ARTICOLO', 'QTA', 'CARTELLINO', 'COMMESSA', 'UTENTE', 'DATA', 'REPARTO', 'LINEA', 'CAUSALE', 'COMPLETA');
$riparazioni = $db->get('riparazioni');

// Calcola il totale dei record
$total_records = count($riparazioni);

// Ora dovresti avere il totale corretto dei record che soddisfano la condizione

// Imposta la variabile per il totale delle pagine
$total_pages = ceil($total_records / $pagelimit);

// Esegui la paginazione con il totale dei record e il limite per pagina
$rows = $db->arraybuilder()->paginate('riparazioni', $page, $select);


include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header">Riparazioni di
                <b>
                    <?php echo $labValue ?>
                </b>
            </h1>
        </div>
    </div>
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>
    <!-- Filters -->
    <div class="well text-center filter-form">
        <form class="form form-inline" action="">
            <label for="input_search">Cerca</label>
            <input type="text" class="form-control" id="input_search" name="search_string" value=""
                placeholder="Descrizione articolo">
            <label for="input_search_linea">Linea</label>
            <input type="text" class="form-control" id="input_search_linea" name="search_string_linea" value=""
                placeholder="Linea">
            <input type="submit" value="Go" class="btn btn-primary">
        </form>
    </div>
    <hr>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th width="3%">ID</th>
                    <th width="13%">CODICE</th>
                    <th width="30%">ARTICOLO</th>
                    <th width="3%">QTA</th>
                    <th width="8%">CARTELLINO</th>
                    <th width="5%">DATA</th>
                    <th width="8%">REPARTO</th>
                    <th width="5%">LINEA</th>
                    <th width="12%">AZIONI</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riparazioni as $row): ?>
                    <tr <?php if ($row['COMPLETA'] == 1)
                        echo ' style="background-color: #dbfbdb;"'; ?>>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['IDRIP']; ?>">
                            <?php echo $row['IDRIP']; ?>
                        </td>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['CODICE']; ?>">
                            <?php echo xss_clean($row['CODICE']); ?>
                        </td>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['ARTICOLO']; ?>">
                            <?php echo xss_clean($row['ARTICOLO']); ?>
                        </td>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['QTA']; ?>">
                            <?php echo xss_clean($row['QTA']); ?>
                        </td>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['CARTELLINO']; ?>">
                            <?php echo xss_clean($row['CARTELLINO']); ?>
                        </td>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['DATA']; ?>">
                            <?php echo xss_clean($row['DATA']); ?>
                        </td>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['REPARTO']; ?>">
                            <?php echo xss_clean($row['REPARTO']); ?>
                        </td>
                        <td style="vertical-align: middle;" data-id="<?php echo $row['LINEA']; ?>">
                            <?php echo xss_clean($row['LINEA']); ?>
                        </td>
                        <input type="hidden" data-id="<?php echo $row['CAUSALE']; ?>">
                        <input type="hidden" data-id="<?php echo $row['UTENTE']; ?>">
                        <td style="vertical-align: middle; text-align:center;">
                            <a id="btnAnteprima" href="#" class="btn btn-primary show-record-details"
                                data-record-id="<?php echo $row['IDRIP']; ?>">
                                <i class="fas fa-search"></i></a>
                            <a id="btnStampa" href="file_preview.php?riparazione_id=<?php echo $row['IDRIP']; ?>"
                                style="background:orange; border:solid 1pt orange;" class="btn btn-primary"><i
                                    class="far fa-print"></i></a>
                            <?php if ($row['COMPLETA'] != 1): ?>
                                <a id="btnCompleta" href="set_completa.php?riparazione_id=<?php echo $row['IDRIP']; ?>"
                                    style="background:green; border:solid 1pt green;" class="btn btn-primary"><i
                                        class="fa fa-check"></i></a>
                            <?php endif ?>
                            <?php if ($row['COMPLETA'] == 1): ?>
                                <a id="btnRimuovi" href="remove_completa.php?riparazione_id=<?php echo $row['IDRIP']; ?>"
                                    style="background:#f55e47; border:solid 1pt #f55e47;" class="btn btn-primary"><i
                                        class="fa fa-minus"></i></a>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- //Table -->
    <div class="modal fade" id="record-details-modal" tabindex="-1" role="dialog"
        aria-labelledby="record-details-modal-label">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="record-details-modal-label">Dettagli</h4>
                </div>
                <div class="modal-body">
                    <!-- Contenuto del modal per i dettagli del record -->
                    <div id="record-details"></div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Pagination -->
    <div class="text-center">
        <?php echo paginationLinks($page, $total_pages, 'lab_riparazioni.php'); ?>
    </div>
    <!-- //Pagination -->
</div>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>
<script>
    $(document).ready(function () {
        // Aggiungi un gestore di eventi al clic sul pulsante "fa-search"
        $('.show-record-details').click(function () {
            var recordId = $(this).data('record-id');

            // Esegui una richiesta AJAX per ottenere i dettagli del record dal server

            $.ajax({
                url: 'get_lab_riparazione_details.php',
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
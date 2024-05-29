<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Costumers class
require_once BASE_PATH . '/lib/Linee/Linee.php';
$associazioni = new Linee();

// Get Input data from query string
$search_string = filter_input(INPUT_GET, 'search_string');
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
    $filter_col = 'sigla';
}
if (!$order_by) {
    $order_by = 'Asc';
}

//Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();
$select = array(
    'ID',
    'sigla',
    'descrizione'
);

//Start building query according to input parameters.
// If search string
if ($search_string) {
    $db->where('descrizione', '%' . $search_string . '%', 'like');
}

//If order by option selected
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

// Set pagination limit
$db->pageLimit = $pagelimit;

// Get result of the query.
$rows = $db->arraybuilder()->paginate('linee', $page, $select);
$total_pages = $db->totalPages;

include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header page-action-links text-left">Tabella Linee</h1>
        </div>
        <div class="col-lg-6">
            <div class="page-action-links text-right">
                <a href="add_linee.php?operation=create" class="btn btn-success"><i class="fas fa-plus"></i> NUOVA</a>
            </div>
        </div>
    </div>
    <hr>
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>

    <!-- Filters -->
    <div class="well text-center filter-form" style="background-color:#f2f2f2;padding:1%;border-radius:20px;">
        <form class="form form-inline" action="">
            <label for="input_search" style="margin-right:1%">Cerca</label>
            <input type="text" class="form-control" id="input_search" name="search_string" value=""
                placeholder="ID Numerata" style="margin-right:1%">
            <label for="input_order" style="margin-right:1%">Ordina per</label>
            <select name="filter_col" class="form-control" style="margin-right:1%">
                <?php
                foreach ($associazioni->setOrderingValues() as $opt_value => $opt_name):
                    ($order_by === $opt_value) ? $selected = 'selected' : $selected = '';
                    echo ' <option value="' . $opt_value . '" ' . $selected . '>' . $opt_name . '</option>';
                endforeach;
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
                ?>>Dec</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <hr>
    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th style="text-align: center;" width="15%">ID</th>
                    <th width="15%">Sigla</th>
                    <th width="55%">Marchio</th>
                    <th width="15%">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr style="text-align: center;">
                        <td style="vertical-align:middle">
                            <?php echo $row['ID']; ?>
                        </td>
                        <td style="vertical-align:middle">
                            <?php echo xss_clean($row['sigla']); ?>
                        </td>
                        <td style="vertical-align:middle">
                            <?php echo xss_clean($row['descrizione']); ?>
                        </td>
                        <td>
                            <a href="#" class="btn btn-danger delete_btn" data-toggle="modal"
                                data-target="#confirm-delete-<?php echo $row['ID']; ?>"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="confirm-delete-<?php echo ($row['ID']); ?>" role="dialog">
                        <div class="modal-dialog">
                            <form action="delete_linee.php" method="POST">
                                <!-- Modal content -->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Conferma</h4>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="del_id" id="del_id" value="<?php echo $row['ID']; ?>">
                                        <p>Sicuro di voler procedere ad eliminare questa linea?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-default pull-left">Si</button>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
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

    <!-- Pagination -->
    <div class="text-center">
        <?php echo paginationLinks($page, $total_pages, 'id_numerate.php'); ?>
    </div>
    <!-- //Pagination -->
</div>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>
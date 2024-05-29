<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Costumers class
require_once BASE_PATH . '/lib/Numerate/Numerate.php';
$associazioni = new Numerate();


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
    $filter_col = 'ID';
}
if (!$order_by) {
    $order_by = 'Asc';
}

//Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();
$select = array(
    'ID',
    'N01',
    'N02',
    'N03',
    'N04',
    'N05',
    'N06',
    'N07',
    'N08',
    'N09',
    'N10',
    'N11',
    'N12',
    'N13',
    'N14',
    'N15',
    'N16',
    'N17',
    'N18',
    'N19',
    'N20'
);

//Start building query according to input parameters.
// If search string
if ($search_string) {
    $db->where('ID', '%' . $search_string . '%', 'like');
    ;
}

//If order by option selected
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

// Set pagination limit
$db->pageLimit = $pagelimit;

// Get result of the query.
$rows = $db->arraybuilder()->paginate('id_numerate', $page, $select);
$total_pages = $db->totalPages;

include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header page-action-links text-left">Tabella Numerate</h1>
        </div>
        <div class="col-lg-6">
            <div class="page-action-links text-right">
                <a href="add_id_numerate.php?operation=create" class="btn btn-success"><i
                        class="glyphicon glyphicon-plus"></i>NUOVA</a>
            </div>
        </div>
    </div>
    <hr>
    <?php include BASE_PATH . '/includes/flash_messages.php'; ?>

    <!-- Filters -->
    <div class="well text-center filter-form" style="background-color:#f2f2f2;padding:1%;border-radius:20px;">
        <form class="form form-inline" action="" style="margin-right:1%">
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
                ?>>Desc</option>
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
                    <th style="text-align: center;" width="35%">ID</th>
                    <th width="3%">N01</th>
                    <th width="3%">N02</th>
                    <th width="3%">N03</th>
                    <th width="3%">N04</th>
                    <th width="3%">N05</th>
                    <th width="3%">N06</th>
                    <th width="3%">N07</th>
                    <th width="3%">N08</th>
                    <th width="3%">N09</th>
                    <th width="3%">N10</th>
                    <th width="3%">N11</th>
                    <th width="3%">N12</th>
                    <th width="3%">N13</th>
                    <th width="3%">N14</th>
                    <th width="3%">N15</th>
                    <th width="3%">N16</th>
                    <th width="3%">N17</th>
                    <th width="3%">N18</th>
                    <th width="3%">N19</th>
                    <th width="3%">N20</th>
                    <th width="5%">AZIONI</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr style="text-align: center;">
                        <td>
                            <?php echo $row['ID']; ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N01']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N02']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N03']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N04']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N05']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N06']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N07']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N08']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N09']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N10']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N11']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N12']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N13']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N14']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N15']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N16']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N17']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N18']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N19']); ?>
                        </td>
                        <td>
                            <?php echo xss_clean($row['N20']); ?>
                        </td>

                        <td>
                            <a href="#" class="btn btn-danger delete_btn" data-toggle="modal"
                                data-target="#confirm-delete-<?php echo $row['ID']; ?>"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="confirm-delete-<?php echo ($row['ID']); ?>" role="dialog">
                        <div class="modal-dialog">
                            <form action="delete_id_numerate.php" method="POST">
                                <!-- Modal content -->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Conferma</h4>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="del_id" id="del_id" value="<?php echo $row['ID']; ?>">
                                        <p>Sicuro di voler procedere ad eliminare questa numerata?</p>
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
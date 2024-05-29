<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Users class
require_once BASE_PATH . '/lib/Modelli/Modelli.php';
$modelli = new Modelli();


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
    $filter_col = 'codice';
}
if (!$order_by) {
    $order_by = 'Asc';
}

//Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();
$select = array('id', 'linea', 'codice', 'descrizione', 'qta_varianti', 'path_to_image');

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
$rows = $db->arraybuilder()->paginate('basi_modelli', $page, $select);
$total_pages = $db->totalPages;

include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header page-action-links text-left">Modelli</h1>
        </div>
        <div class="col-lg-6">
            <div class="page-action-links text-right">
                <a href="add_modelli.php" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i> NUOVO</a>
            </div>
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
    <div class="well text-center filter-form" style="background-color:#f2f2f2;padding:1%;border-radius:20px;">
        <form class="form form-inline" action="">
            <label for="input_search"style="margin-right:1%">Cerca</label>
            <input type="text" class="form-control" id="input_search" name="search_string" value=""
                placeholder="Descrizione"style="margin-right:1%">
            <label for="input_order"style="margin-right:1%">Ordina per</label>
            <select name="filter_col" class="form-control"style="margin-right:1%">
                <?php
                foreach ($modelli->setOrderingValues() as $opt_value => $opt_name):
                    ($order_by === $opt_value) ? $selected = 'selected' : $selected = '';
                    echo ' <option value="' . $opt_value . '" ' . $selected . '>' . $opt_name . '</option>';
                endforeach;
                ?>
            </select>
            <select name="order_by" class="form-control" id="input_order"style="margin-right:1%">
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
            <input type="submit" value="Go" class="btn btn-primary">
        </form>
    </div>
    <hr>
    <!-- //Filters -->

    <!-- Table -->
    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th width="5%">Immagine</th>
                <th width="15%">Codice</th>
                <th width="58%">Descrizione</th>
                <th width="5%">Varianti</th>
                <th width="2%">Linea</th>
                <th width="15%">Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td style="vertical-align: middle; align-item:center;">
                        <?php
                        $imageSrc = empty($row['path_to_image']) ? '../../src/img/default.jpg' : '../../' . $row['path_to_image'];
                        echo '<img src="' . htmlspecialchars($imageSrc) . '" alt="Immagine" style="width: 100px; height: 100px; border: solid 1pt lightgrey">';
                        ?>
                    </td>

                    <td style="vertical-align: middle;">
                        <?php echo htmlspecialchars($row['codice']); ?>
                    </td>
                    <td style="vertical-align: middle;">
                        <?php echo htmlspecialchars($row['descrizione']); ?>
                    </td>
                    <td style="vertical-align: middle; text-align:center;">
                        <?php echo htmlspecialchars($row['qta_varianti']); ?>
                    </td>
                    <td style="vertical-align: middle; text-align:center;">
                        <?php echo htmlspecialchars($row['linea']); ?>
                    </td>
                    <td style="vertical-align: middle;  text-align:center;">
                        <a href="edit_modello.php?id_modello=<?php echo $row['id']; ?>&operation=plus"
                            class="btn btn-success"><i class="fas fa-plus"></i></a>
                        <a href="edit_modello.php?id_modello=<?php echo $row['id']; ?>&operation=edit"
                            class="btn btn-primary"><i class="fas fa-pencil"></i></a>
                        <a href="#" class="btn btn-danger delete_btn" data-toggle="modal"
                            data-target="#confirm-delete-<?php echo $row['id']; ?>"><i
                                class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="confirm-delete-<?php echo $row['id']; ?>" role="dialog">
                    <div class="modal-dialog">
                        <form action="delete_modello.php" method="POST">
                            <!-- Modal content -->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">Conferma</h4>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="del_id" id="del_id" value="<?php echo $row['id']; ?>">
                                    <p>Sicuro di voler eliminare questa riga?</p>
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
                echo '<li' . $li_class . '><a href="modelli.php' . $http_query . '&page=' . $i . '">' . $i . '</a></li>';
            }
            echo '</ul>';
        }
        ?>
    </div>
    <!-- //Pagination -->
</div>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php'; ?>
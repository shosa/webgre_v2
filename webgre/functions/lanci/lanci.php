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
$select = array('lanci.ID', 'lanci.data', 'lanci.lancio', 'lanci.avanzamento', 'lanci.id_lab', 'SUM(lanci.paia) AS paia_total', 'lanci.id_modello', 'linee.descrizione', 'lanci.id_variante', 'lanci.stato', 'lanci.paia', 'basi_modelli.descrizione AS modello_descrizione', 'var_modelli.desc_variante AS variante_descrizione');
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
        <div class="col-lg-6">
            <h1 class="page-header page-action-links text-left">Lanci</h1>
        </div>
        <div class="col-lg-6">
            <div class="page-action-links text-right">
                <a href="add_lancio.php" class="btn btn-success"><i class="fas fa-plus"></i> NUOVO</a>
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
    <div class="well text-center filter-form">
        <form class="form form-inline" action="">
            <label for="input_search">Cerca</label>
            <input type="text" class="form-control" id="input_search" name="search_string" value=""
                placeholder="N° Lancio">
            <label for="input_order">Ordina per</label>
            <select name="filter_col" class="form-control">
                <?php
                foreach ($modelli->setOrderingValues() as $opt_value => $opt_name):
                    ($order_by === $opt_value) ? $selected = 'selected' : $selected = '';
                    echo ' <option value="' . $opt_value . '" ' . $selected . '>' . $opt_name . '</option>';
                endforeach;
                ?>
            </select>
            <select name="order_by" class="form-control" id="input_order">
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
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th width="10%">Lancio</th>
                    <th width="10%">Data di Lancio</th>
                    <th width="30%">Linea</th>
                    <th width="10%">Paia</th>
                    <th width="10%">Stato</th>
                    <th width="20%">In Lavorazione presso</th>
                    <th width="10%">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td style="vertical-align: middle;">
                            <?php echo htmlspecialchars($row['lancio']); ?>
                        </td>
                        <td style="vertical-align: middle;">
                            <span class="stato <?php echo ($row['stato'] === 'IN ATTESA') ? 'attesa' : ''; ?>">
                                <?php echo htmlspecialchars($row['data']); ?>
                            </span>
                        </td>
                        <!-- Mostra la concatenazione delle descrizioni dei modelli e varianti -->
                        <td style="vertical-align: middle;">
                            <?php echo htmlspecialchars($row['descrizione']); ?>
                        </td>
                        <td style="vertical-align: middle;">
                            <?php echo htmlspecialchars($row['paia_total']); ?>
                        </td>
                        <td style="vertical-align: middle;">
                            <span class="stato <?php echo ($row['stato'] === 'IN ATTESA') ? 'attesa' : ''; ?>">
                                <b>
                                    <?php echo htmlspecialchars($row['stato']); ?>
                                </b>
                            </span>
                        </td>

                        <td style="vertical-align: middle;">
                            <?php
                            if ($row['id_lab'] !== NULL) {
                                $db = getDbInstance();
                                $laboratorio = $db->where('ID', $row['id_lab'])->getOne('laboratori');
                                if ($laboratorio) {
                                    echo '<span>' . htmlspecialchars($laboratorio['Nome']) . '</span>';
                                }
                            } else {
                                echo '<span class="stato attesa">DA ASSEGNARE</span>';
                            }
                            ?>
                        </td>
                        <td style="vertical-align: middle;">
                            <a href="open_lanci.php?lancio=<?php echo $row['lancio']; ?>&operation=plus"
                                class="btn btn-warning"><i class="fa fa-folder-open"></i></a>
                            <a href="#" class="btn btn-danger delete_btn" data-toggle="modal"
                                data-target="#confirm-delete-<?php echo $row['lancio']; ?>"><i
                                    class="fas fa-trash"></i></a>
                        </td>
                        <input type="hidden" name="hidden_id" value="<?php echo htmlspecialchars($row['ID']); ?>">
                    </tr>
                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="confirm-delete-<?php echo $row['lancio']; ?>" role="dialog">
                        <div class="modal-dialog">
                            <form action="delete_lancio.php" method="POST">
                                <!-- Modal content -->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Conferma</h4>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="del_id" id="del_id"
                                            value="<?php echo $row['lancio']; ?>">
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
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
$username = $_SESSION['username'];
$labUserRow = $db->where('user', $username)->getOne('lab_user', 'lab');
$labValue = $labUserRow['lab'];
$select = array(
    'DISTINCT var_modelli.ID',
    'var_modelli.nome_completo',
    'var_modelli.id_modello',
    'var_modelli.path_diba',
    'var_modelli.path_pic',
    'basi_modelli.path_to_image',
    'basi_modelli.linea',
    'lanci.id_lab',
    'linee.descrizione'
);

$db->join('lanci', 'var_modelli.ID = lanci.id_variante', 'INNER');
$db->join('basi_modelli', 'var_modelli.id_modello = basi_modelli.ID', 'INNER');
$db->join('laboratori', 'laboratori.ID = lanci.id_lab', 'INNER');
$db->join('lab_user', 'laboratori.Nome = lab_user.lab', 'INNER');
$db->join('linee', 'basi_modelli.linea = linee.sigla', 'INNER');
$db->where('lab_user.user', $username);
$db->orderBy('var_modelli.nome_completo', 'ASC');
// If there is a search string
if ($search_string) {
    $db->where('var_modelli.nome_completo', '%' . $search_string . '%', 'like');
}

// Set pagination limit
$db->pageLimit = $pagelimit;

// Get the results of the query.
$rows = $db->arraybuilder()->paginate('var_modelli', $page, $select);
$total_pages = $db->totalPages;

include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header">Modelli Assegnati a <b>
                    <?php echo $labValue ?>
                </b></h1>
        </div>
    </div>
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
                placeholder="Descrizione">

            <input type="submit" value="Go" class="btn btn-primary">
        </form>
    </div>
    <hr>
    <!-- //Filters -->

    <!-- Table -->
    <?php
    $lastLinea = null;
    ?>

    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th width="5%">Immagine</th>
                <th width="60%">Articolo</th>
                <th width="15%">Diba</th>
                <th width="15%">Schema Taglio</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <?php if ($row['linea'] !== $lastLinea): // Check if Linea has changed ?>
                    <tr style="background:grey;color:white;">
                        <th colspan="4">
                            <?php echo htmlspecialchars($row['descrizione']); ?>
                        </th>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td style="vertical-align: middle; align-item:center;">
                        <?php
                        $imageSrc = empty($row['path_to_image']) ? '../../src/img/default.jpg' : '../../' . $row['path_to_image'];
                        echo '<img src="' . htmlspecialchars($imageSrc) . '" alt="Immagine" style="max-width: 100px; max-height: 100px; border: solid 1pt lightgrey">';
                        ?>
                    </td>
                    <td style="vertical-align: middle;">
                        <?php echo htmlspecialchars($row['nome_completo']); ?>
                    </td>

                    <td style="vertical-align: middle; text-align:center;">
                        <?php if (!empty($row['path_diba'])): ?>
                            <a href="<?php echo htmlspecialchars($row['path_diba']); ?>" download>
                                <i class="fad fa-file-pdf fa-lg"
                                    style="font-size: 30pt; --fa-primary-color: #f00000; --fa-secondary-color: #e0d7d7;"></i>
                            </a>
                        <?php else: ?>
                            <a href="#" onclick="showPopup('FILE NON DISPONIBILE AL MOMENTO')">
                                <i class="fad fa-file-pdf fa-lg"
                                    style="font-size: 30pt; --fa-primary-color: #f00000; --fa-secondary-color: #e0d7d7;"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                    <td style="vertical-align: middle; text-align:center;">
                        <?php if (!empty($row['path_pic'])): ?>
                            <a href="<?php echo htmlspecialchars($row['path_pic']); ?>" download>
                                <i class="fad fa-images"
                                    style="font-size: 30pt; --fa-primary-color: #4f89ee; --fa-secondary-color: #41aa64;"></i>
                            </a>
                        <?php else: ?>
                            <a href="#" onclick="showPopup('FILE NON DISPONIBILE AL MOMENTO')">
                                <i class="fad fa-images"
                                    style="font-size: 30pt; --fa-primary-color: #4f89ee; --fa-secondary-color: #41aa64;"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <script>
                    function showPopup(message) {
                        alert(message);
                    }
                </script>
                <?php
                // Update lastLinea with the current Linea
                $lastLinea = $row['linea'];
                ?>
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
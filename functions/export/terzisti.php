<?php
/**
 * Gestione Terzisti
 * 
 * Questo script gestisce la visualizzazione, paginazione e ricerca dei terzisti.
 */
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once BASE_PATH . '/utils/helpers.php';
require_once BASE_PATH . '/utils/log_utils.php';

// Recupero parametri dalla query string con validazione
$search_string = filter_input(INPUT_GET, 'search_string', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$filter_col = filter_input(INPUT_GET, 'filter_col', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'id';
$order_by = filter_input(INPUT_GET, 'order_by', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'Desc';
$del_id = filter_input(INPUT_GET, 'del_id', FILTER_VALIDATE_INT);
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;

// Impostazioni paginazione
$pagelimit = 20;

// Validazione dei parametri di ordinamento
$allowed_columns = ['id', 'ragione_sociale', 'nazione', 'consegna'];
$allowed_orders = ['Asc', 'Desc'];

// Validazione dei parametri di filtro
if (!in_array($filter_col, $allowed_columns)) {
    $filter_col = 'id';
}

if (!in_array($order_by, $allowed_orders)) {
    $order_by = 'Desc';
}

// Ottieni istanza del database
$conn = getDbInstance();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Costruzione della query in base ai parametri
try {
    // Preparazione query base
    $sql = "SELECT id, ragione_sociale, nazione, consegna FROM exp_terzisti";
    $params = [];
    
    // Filtraggio per ricerca
    if (!empty($search_string)) {
        $sql .= " WHERE ragione_sociale LIKE :search";
        $params[':search'] = "%{$search_string}%";
    }
    
    // Ordinamento
    $sql .= " ORDER BY {$filter_col} {$order_by}";
    
    // Calcolo offset per paginazione
    $offset = ($page - 1) * $pagelimit;
    
    // Aggiunta limit per paginazione
    $sql .= " LIMIT :limit OFFSET :offset";
    $params[':limit'] = $pagelimit;
    $params[':offset'] = $offset;
    
    // Preparazione ed esecuzione della query
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query per conteggio totale record per la paginazione
    $count_sql = "SELECT COUNT(*) as total FROM exp_terzisti";
    $count_params = [];
    
    if (!empty($search_string)) {
        $count_sql .= " WHERE ragione_sociale LIKE :search";
        $count_params[':search'] = "%{$search_string}%";
    }
    
    $count_stmt = $conn->prepare($count_sql);
    foreach ($count_params as $key => $value) {
        $count_stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $count_stmt->execute();
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_records / $pagelimit);
    
} catch (PDOException $e) {
    // Gestione degli errori
    error_log("Errore database: " . $e->getMessage());
    $rows = [];
    $total_pages = 0;
}

include(BASE_PATH . "/components/header.php");
?>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include(BASE_PATH . "/components/navbar.php"); ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include(BASE_PATH . "/components/topbar.php"); ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php require_once(BASE_PATH . "/utils/alerts.php"); ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Terzisti</h1>
                        <a href="add_terzista.php" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Nuovo Terzista
                        </a>
                    </div>
                    
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Terzisti</li>
                    </ol>
                    
                    <div class="row">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Lista Terzisti</h6>
                                    <div class="dropdown no-arrow">
                                        <form action="" method="get" class="form-inline">
                                            <div class="input-group">
                                                <input type="text" name="search_string" class="form-control form-control-sm" 
                                                       placeholder="Cerca per ragione sociale" 
                                                       value="<?= htmlspecialchars($search_string) ?>">
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary btn-sm" type="submit">
                                                        <i class="fas fa-search fa-sm"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Ragione Sociale</th>
                                                    <th>Nazione</th>
                                                    <th>Consegna</th>
                                                    <th>Azioni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($rows)): ?>
                                                    <?php foreach ($rows as $row): ?>
                                                        <tr>
                                                            <td><?= $row['id'] ?></td>
                                                            <td><?= htmlspecialchars($row['ragione_sociale']) ?></td>
                                                            <td><?= htmlspecialchars($row['nazione']) ?></td>
                                                            <td><?= htmlspecialchars($row['consegna']) ?></td>
                                                            <td>
                                                                <a href="edit_terzista.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="javascript:void(0);" class="btn btn-danger btn-sm" 
                                                                   onclick="confirmDelete(<?= $row['id'] ?>)">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">Nessun risultato trovato</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Paginazione -->
                                    <?php if ($total_pages > 1): ?>
                                        <nav aria-label="Paginazione">
                                            <ul class="pagination justify-content-center">
                                                <?php 
                                                // Costruzione query string per mantenere i parametri di ricerca
                                                $query_params = $_GET;
                                                unset($query_params['page']); // Rimuovi il parametro page
                                                $http_query = !empty($query_params) ? '?' . http_build_query($query_params) . '&' : '?';
                                                
                                                // Pulsante pagina precedente
                                                if ($page > 1): 
                                                ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="terzisti.php<?= $http_query ?>page=<?= $page - 1 ?>">
                                                            <i class="fas fa-chevron-left"></i>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <?php 
                                                // Numerazione pagine
                                                for ($i = 1; $i <= $total_pages; $i++): 
                                                    $active = ($page == $i) ? ' active' : '';
                                                ?>
                                                    <li class="page-item<?= $active ?>">
                                                        <a class="page-link" href="terzisti.php<?= $http_query ?>page=<?= $i ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                                
                                                <?php 
                                                // Pulsante pagina successiva
                                                if ($page < $total_pages): 
                                                ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="terzisti.php<?= $http_query ?>page=<?= $page + 1 ?>">
                                                            <i class="fas fa-chevron-right"></i>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                    <!-- //Paginazione -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->
    
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    
    <!-- Modal di conferma eliminazione -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Conferma eliminazione</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Sei sicuro di voler eliminare questo terzista?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Elimina</a>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
<script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>

<script>
function confirmDelete(id) {
    document.getElementById('confirmDelete').href = 'delete_terzista.php?id=' + id;
    $('#deleteModal').modal('show');
}
</script>
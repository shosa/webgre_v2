<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../config/config.php';
include(BASE_PATH . "/components/header.php");
require_once BASE_PATH . '/components/auth_validate.php';

// Generazione token CSRF se non esiste
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Impostazioni paginazione e filtri
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$records_per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 25;
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$type_filter = isset($_GET['activity_type']) ? $_GET['activity_type'] : '';

// Calcola l'offset per la paginazione
$offset = ($page - 1) * $records_per_page;

// Preparazione della query di base
$conn = getDbInstance();
$base_query = "FROM activity_log 
               JOIN utenti ON activity_log.user_id = utenti.id 
               WHERE 1=1";

// Aggiungi filtri alla query
$params = [];
if (!empty($from_date)) {
    $base_query .= " AND DATE(activity_log.created_at) >= :from_date";
    $params[':from_date'] = $from_date;
}
if (!empty($to_date)) {
    $base_query .= " AND DATE(activity_log.created_at) <= :to_date";
    $params[':to_date'] = $to_date;
}
if ($user_filter > 0) {
    $base_query .= " AND activity_log.user_id = :user_id";
    $params[':user_id'] = $user_filter;
}
if (!empty($category_filter)) {
    $base_query .= " AND activity_log.category = :category";
    $params[':category'] = $category_filter;
}
if (!empty($type_filter)) {
    $base_query .= " AND activity_log.activity_type = :activity_type";
    $params[':activity_type'] = $type_filter;
}

// Query per il conteggio totale dei record
$count_query = "SELECT COUNT(*) as total " . $base_query;
$stmt = $conn->prepare($count_query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Query per i dati paginati
$data_query = "SELECT activity_log.*, utenti.user_name " . $base_query . " 
               ORDER BY activity_log.id DESC 
               LIMIT :offset, :limit";
$stmt = $conn->prepare($data_query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$activity_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query per ottenere tutti gli utenti per il filtro
$users_query = "SELECT id, user_name FROM utenti ORDER BY user_name";
$stmt = $conn->prepare($users_query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query per ottenere tutte le categorie uniche per il filtro
$categories_query = "SELECT DISTINCT category FROM activity_log WHERE category != '' ORDER BY category";
$stmt = $conn->prepare($categories_query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query per ottenere tutti i tipi di attività unici per il filtro
$types_query = "SELECT DISTINCT activity_type FROM activity_log WHERE activity_type != '' ORDER BY activity_type";
$stmt = $conn->prepare($types_query);
$stmt->execute();
$activity_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Log Attività</h1>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="exportBtn">
                            <i class="fas fa-download fa-sm text-white-50"></i> Esporta
                        </a>
                    </div>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item active">Log Attività Generale</li>
                    </ol>

                    <!-- Filtri -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Filtri</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="" class="form-inline">
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="from_date" class="sr-only">Da</label>
                                    <input type="date" class="form-control" id="from_date" name="from_date" 
                                           value="<?php echo htmlspecialchars($from_date); ?>" placeholder="Da">
                                </div>
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="to_date" class="sr-only">A</label>
                                    <input type="date" class="form-control" id="to_date" name="to_date" 
                                           value="<?php echo htmlspecialchars($to_date); ?>" placeholder="A">
                                </div>
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="user_id" class="sr-only">Utente</label>
                                    <select class="form-control" id="user_id" name="user_id">
                                        <option value="0">Tutti gli utenti</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo ($user_filter == $user['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['user_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="category" class="sr-only">Categoria</label>
                                    <select class="form-control" id="category" name="category">
                                        <option value="">Tutte le categorie</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['category']; ?>" <?php echo ($category_filter == $category['category']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['category']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="activity_type" class="sr-only">Tipo</label>
                                    <select class="form-control" id="activity_type" name="activity_type">
                                        <option value="">Tutti i tipi</option>
                                        <?php foreach ($activity_types as $type): ?>
                                            <option value="<?php echo $type['activity_type']; ?>" <?php echo ($type_filter == $type['activity_type']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($type['activity_type']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="per_page" class="sr-only">Righe per pagina</label>
                                    <select class="form-control" id="per_page" name="per_page">
                                        <option value="25" <?php echo ($records_per_page == 25) ? 'selected' : ''; ?>>25 per pagina</option>
                                        <option value="50" <?php echo ($records_per_page == 50) ? 'selected' : ''; ?>>50 per pagina</option>
                                        <option value="100" <?php echo ($records_per_page == 100) ? 'selected' : ''; ?>>100 per pagina</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary mb-2">Filtra</button>
                                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary mb-2 ml-2">Reset</a>
                            </form>
                        </div>
                    </div>

                    <!-- Tabella Log -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Log Attività Admin</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Opzioni:</div>
                                    <a class="dropdown-item" href="#" id="toggleColumnsBtn">Mostra/Nascondi Colonne</a>
                                    <a class="dropdown-item" href="#" id="refreshTableBtn">Aggiorna Tabella</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Info Risultati -->
                            <div class="mb-3">
                                <p>Mostrando <?php echo min($total_records, $records_per_page); ?> di <?php echo $total_records; ?> risultati</p>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Utente</th>
                                            <th>Categoria</th>
                                            <th>Tipo</th>
                                            <th>Descrizione</th>
                                            <th>Note</th>
                                            <th>Dettagli</th>
                                            <th>Data/Ora</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Iterazione attraverso le righe del risultato della query
                                        if (count($activity_logs) > 0) {
                                            foreach ($activity_logs as $log) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($log['id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($log['user_name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($log['category']) . "</td>";
                                                echo "<td>" . htmlspecialchars($log['activity_type']) . "</td>";
                                                echo "<td>" . htmlspecialchars($log['description']) . "</td>";
                                                echo "<td>" . htmlspecialchars($log['note']) . "</td>";
                                                
                                                // Colonna dettagli
                                                echo '<td class="text-center">';
                                                if (!empty($log['text_query'])) {
                                                    echo "<button class='btn btn-sm btn-info view-query' data-query-id='" . 
                                                         htmlspecialchars($log['id']) . "'><i class='fas fa-search'></i></button>";
                                                } else {
                                                    echo "<span class='text-muted'>-</span>";
                                                }
                                                echo '</td>';
                                                
                                                // Formatta data e ora
                                                $datetime = new DateTime($log['created_at']);
                                                echo "<td>" . $datetime->format('d/m/Y H:i:s') . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='8' class='text-center'>Nessun risultato trovato</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginazione -->
                            <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-4">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo buildPaginationUrl($page - 1); ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($start_page + 4, $total_pages);
                                    
                                    if ($start_page > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="' . buildPaginationUrl(1) . '">1</a></li>';
                                        if ($start_page > 2) {
                                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                        }
                                    }
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++) {
                                        echo '<li class="page-item ' . (($page == $i) ? 'active' : '') . '">
                                                <a class="page-link" href="' . buildPaginationUrl($i) . '">' . $i . '</a>
                                              </li>';
                                    }
                                    
                                    if ($end_page < $total_pages) {
                                        if ($end_page < $total_pages - 1) {
                                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                        }
                                        echo '<li class="page-item"><a class="page-link" href="' . buildPaginationUrl($total_pages) . '">' . $total_pages . '</a></li>';
                                    }
                                    ?>
                                    
                                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo buildPaginationUrl($page + 1); ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            
            <!-- MODALE QUERY -->
            <div class="modal fade" id="queryModal" tabindex="-1" role="dialog" aria-labelledby="queryModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="queryModalLabel">Dettagli Query</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <textarea id="queryText" class="form-control" rows="10" readonly></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                            <button type="button" class="btn btn-primary" id="copyQueryBtn">Copia</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Colonne -->
            <div class="modal fade" id="columnsModal" tabindex="-1" role="dialog" aria-labelledby="columnsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="columnsModalLabel">Gestione Colonne</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group" id="columnToggleOptions">
                                <!-- Opzioni generate dinamicamente via JavaScript -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                            <button type="button" class="btn btn-primary" id="saveColumnsBtn">Salva</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include(BASE_PATH . "/components/footer.php"); ?>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- JavaScript -->
    <script src="<?php echo BASE_URL ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php echo BASE_URL ?>/js/sb-admin-2.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/dataTables.buttons.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/jszip/jszip.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/pdfmake/pdfmake.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/pdfmake/vfs_fonts.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.html5.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.print.min.js"></script>
    <script src="<?php echo BASE_URL ?>/vendor/datatables/buttons.colVis.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gestione visualizzazione query
        const viewButtons = document.querySelectorAll('.view-query');
        viewButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const queryId = this.dataset.queryId;
                // Effettua una richiesta AJAX per ottenere il testo completo della query
                $.ajax({
                    url: 'get_query.php',
                    method: 'POST',
                    data: { query_id: queryId },
                    dataType: 'json',
                    success: function (data) {
                        if (data.status === 'success') {
                            document.getElementById('queryText').textContent = data.query;
                            $('#queryModal').modal('show');
                        } else {
                            alert('Errore: ' + data.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Risposta ricevuta:', xhr.responseText);
                        alert('Si è verificato un errore durante la richiesta: ' + error);
                    }
                });
            });
        });

        // Copia negli appunti
        const copyQueryBtn = document.getElementById('copyQueryBtn');
        if (copyQueryBtn) {
            copyQueryBtn.addEventListener('click', function() {
                const queryText = document.getElementById('queryText');
                queryText.select();
                document.execCommand('copy');
                
                // Feedback visivo
                const originalText = this.textContent;
                this.textContent = 'Copiato!';
                setTimeout(() => {
                    this.textContent = originalText;
                }, 2000);
            });
        }

        // Gestione colonne
        const tableColumns = [
            { name: 'ID', visible: true },
            { name: 'Utente', visible: true },
            { name: 'Categoria', visible: true },
            { name: 'Tipo', visible: true },
            { name: 'Descrizione', visible: true },
            { name: 'Note', visible: true },
            { name: 'Dettagli', visible: true },
            { name: 'Data/Ora', visible: true }
        ];

        // Carica preferenze salvate
        const loadColumnPreferences = () => {
            const savedPreferences = localStorage.getItem('logActivityColumns');
            if (savedPreferences) {
                try {
                    const preferences = JSON.parse(savedPreferences);
                    // Aggiorna le preferenze di visibilità delle colonne
                    preferences.forEach((pref, index) => {
                        if (index < tableColumns.length) {
                            tableColumns[index].visible = pref.visible;
                        }
                    });
                    // Applica le preferenze
                    applyColumnVisibility();
                } catch (e) {
                    console.error('Errore nel caricamento delle preferenze:', e);
                }
            }
        };

        // Applica visibilità colonne
        const applyColumnVisibility = () => {
            const table = document.getElementById('dataTable');
            const headerRow = table.querySelector('thead tr');
            const dataRows = table.querySelectorAll('tbody tr');
            
            if (headerRow) {
                const headerCells = headerRow.querySelectorAll('th');
                
                tableColumns.forEach((column, index) => {
                    if (index < headerCells.length) {
                        headerCells[index].style.display = column.visible ? '' : 'none';
                    }
                    
                    dataRows.forEach(row => {
                        const cells = row.querySelectorAll('td');
                        if (index < cells.length) {
                            cells[index].style.display = column.visible ? '' : 'none';
                        }
                    });
                });
            }
        };

        // Mostra modal gestione colonne
        const toggleColumnsBtn = document.getElementById('toggleColumnsBtn');
        if (toggleColumnsBtn) {
            toggleColumnsBtn.addEventListener('click', function() {
                const container = document.getElementById('columnToggleOptions');
                container.innerHTML = '';
                
                tableColumns.forEach((column, index) => {
                    const div = document.createElement('div');
                    div.className = 'form-check';
                    div.innerHTML = `
                        <input class="form-check-input" type="checkbox" id="column${index}" ${column.visible ? 'checked' : ''}>
                        <label class="form-check-label" for="column${index}">
                            ${column.name}
                        </label>
                    `;
                    container.appendChild(div);
                });
                
                $('#columnsModal').modal('show');
            });
        }

        // Salva preferenze colonne
        const saveColumnsBtn = document.getElementById('saveColumnsBtn');
        if (saveColumnsBtn) {
            saveColumnsBtn.addEventListener('click', function() {
                tableColumns.forEach((column, index) => {
                    column.visible = document.getElementById(`column${index}`).checked;
                });
                
                localStorage.setItem('logActivityColumns', JSON.stringify(tableColumns));
                applyColumnVisibility();
                $('#columnsModal').modal('hide');
            });
        }

        // Aggiorna tabella
        const refreshTableBtn = document.getElementById('refreshTableBtn');
        if (refreshTableBtn) {
            refreshTableBtn.addEventListener('click', function() {
                window.location.reload();
            });
        }

        // Carica preferenze al caricamento della pagina
        loadColumnPreferences();
    });
    </script>
</body>

<?php
// Funzione per costruire l'URL di paginazione mantenendo i filtri
function buildPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return $_SERVER['PHP_SELF'] . '?' . http_build_query($params);
}
?>
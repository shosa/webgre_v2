<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Ottieni l'istanza del database
$pdo = getDbInstance();

// Gestione delle operazioni
$operationMessage = '';
$operationType = '';

// Gestione dell'eliminazione
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (isset($_GET['token']) && $_GET['token'] == $_SESSION['csrf_token']) {
        try {
            $id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
            
            // Prima otteniamo le informazioni del macchinario per il messaggio
            $stmt = $pdo->prepare("SELECT matricola FROM mac_anag WHERE id = ?");
            $stmt->execute([$id]);
            $macchinario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Poi eliminiamo
            $stmt = $pdo->prepare("DELETE FROM mac_anag WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                $operationMessage = "Macchinario con matricola <strong>" . htmlspecialchars($macchinario['matricola']) . "</strong> eliminato con successo.";
                $operationType = "success";
            } else {
                $operationMessage = "Macchinario non trovato o già eliminato.";
                $operationType = "warning";
            }
        } catch (PDOException $e) {
            $operationMessage = "Errore durante l'eliminazione: " . $e->getMessage();
            $operationType = "danger";
        }
    } else {
        $operationMessage = "Token di sicurezza non valido.";
        $operationType = "danger";
    }
}

// Impostazione filtri di ricerca e ordinamento
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipologia = isset($_GET['tipologia']) ? $_GET['tipologia'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'data_creazione';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$itemsPerPage = 15;

// Generazione token CSRF per operazioni di sicurezza
$_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));

// Query di base
$queryCount = "SELECT COUNT(*) FROM mac_anag WHERE 1=1";
$query = "SELECT * FROM mac_anag WHERE 1=1";

// Aggiunta dei filtri
$params = [];

if (!empty($search)) {
    $query .= " AND (matricola LIKE ? OR produttore LIKE ? OR modello LIKE ? OR note LIKE ?)";
    $queryCount .= " AND (matricola LIKE ? OR produttore LIKE ? OR modello LIKE ? OR note LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

if (!empty($tipologia)) {
    $query .= " AND tipologia = ?";
    $queryCount .= " AND tipologia = ?";
    $params[] = $tipologia;
}

// Ordinamento
$validSortColumns = ['matricola', 'tipologia', 'produttore', 'modello', 'data_acquisto', 'data_creazione'];
$validSortOrders = ['ASC', 'DESC'];

if (!in_array($sort, $validSortColumns)) {
    $sort = 'data_creazione';
}

if (!in_array($order, $validSortOrders)) {
    $order = 'DESC';
}

$query .= " ORDER BY $sort $order";

// Paginazione
$stmtCount = $pdo->prepare($queryCount);
$stmtCount->execute($params);
$totalItems = $stmtCount->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);
$page = min($page, max(1, $totalPages));
$offset = ($page - 1) * $itemsPerPage;

$query .= " LIMIT $offset, $itemsPerPage";

// Esecuzione query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$macchinari = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recupero tipi macchine per filtro
$stmtTipi = $pdo->query("SELECT DISTINCT tipologia FROM mac_anag ORDER BY tipologia");
$tipi_macchine = $stmtTipi->fetchAll(PDO::FETCH_COLUMN);

// Inclusione dell'header
require_once BASE_PATH . '/components/header.php';
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
                    <?php include(BASE_PATH . "/utils/alerts.php"); ?>
                    
                    <?php if (!empty($operationMessage)): ?>
                    <div class="alert alert-<?= $operationType ?> alert-dismissible fade show" role="alert">
                        <?= $operationMessage ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Macchinari Aziendali</h1>
                        <div>
                            <a href="new" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Nuovo Macchinario
                            </a>
                            <?php if (!empty($search) || !empty($tipologia)): ?>
                            <a href="lista_macchinari" class="btn btn-secondary btn-sm shadow-sm ml-2">
                                <i class="fas fa-undo fa-sm text-white-50"></i> Reset Filtri
                            </a>
                            <?php endif; ?>
                            <a href="#" id="esportaExcel" class="btn btn-primary btn-sm shadow-sm ml-2">
                                <i class="fas fa-file-excel fa-sm text-white-50"></i> Esporta Excel
                            </a>
                            <a href="#" id="esportaPdf" class="btn btn-danger btn-sm shadow-sm ml-2">
                                <i class="fas fa-file-pdf fa-sm text-white-50"></i> Esporta PDF
                            </a>
                            <a href="home" class="btn btn-info btn-sm shadow-sm ml-2">
                                <i class="fas fa-home fa-sm text-white-50"></i> Home Macchinari
                            </a>
                        </div>
                    </div>
                    
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Macchinari</a></li>
                        <li class="breadcrumb-item active">Lista Macchinari</li>
                    </ol>
                    
                    <!-- Filtri di ricerca -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Filtri di Ricerca</h6>
                            <button class="btn btn-link btn-sm" type="button" data-toggle="collapse" data-target="#collapseFilters">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="card-body collapse show" id="collapseFilters">
                            <form method="GET" action="" class="form-inline">
                                <div class="form-group mb-2 mr-2">
                                    <label for="search" class="sr-only">Ricerca</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="search" name="search" placeholder="Matricola, Produttore, Modello..." value="<?= htmlspecialchars($search) ?>">
                                    </div>
                                </div>
                                <div class="form-group mb-2 mr-2">
                                    <label for="tipologia" class="sr-only">Tipologia</label>
                                    <select class="form-control" id="tipologia" name="tipologia">
                                        <option value="">-- Tutte le Tipologie --</option>
                                        <?php foreach ($tipi_macchine as $tipo): ?>
                                            <option value="<?= htmlspecialchars($tipo) ?>" <?= ($tipologia == $tipo) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tipo) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group mb-2 mr-2">
                                    <label for="sort" class="sr-only">Ordinamento</label>
                                    <select class="form-control" id="sort" name="sort">
                                        <option value="data_creazione" <?= ($sort == 'data_creazione') ? 'selected' : '' ?>>Data Inserimento</option>
                                        <option value="matricola" <?= ($sort == 'matricola') ? 'selected' : '' ?>>Matricola</option>
                                        <option value="tipologia" <?= ($sort == 'tipologia') ? 'selected' : '' ?>>Tipologia</option>
                                        <option value="produttore" <?= ($sort == 'produttore') ? 'selected' : '' ?>>Produttore</option>
                                        <option value="modello" <?= ($sort == 'modello') ? 'selected' : '' ?>>Modello</option>
                                        <option value="data_acquisto" <?= ($sort == 'data_acquisto') ? 'selected' : '' ?>>Data Acquisto</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2 mr-2">
                                    <label for="order" class="sr-only">Direzione</label>
                                    <select class="form-control" id="order" name="order">
                                        <option value="DESC" <?= ($order == 'DESC') ? 'selected' : '' ?>>Decrescente</option>
                                        <option value="ASC" <?= ($order == 'ASC') ? 'selected' : '' ?>>Crescente</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary mb-2">Applica Filtri</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tabella Macchinari -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Elenco Macchinari 
                                <span class="badge badge-secondary"><?= $totalItems ?> risultati</span>
                                <?php if (!empty($search)): ?>
                                <span class="badge badge-info">Ricerca: <?= htmlspecialchars($search) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($tipologia)): ?>
                                <span class="badge badge-info">Tipo: <?= htmlspecialchars($tipologia) ?></span>
                                <?php endif; ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (count($macchinari) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="macchinari-table" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Matricola</th>
                                                <th>Tipologia</th>
                                                <th>Produttore</th>
                                                <th>Modello</th>
                                                <th>Data Acquisto</th>
                                                <th>Stato</th>
                                                <th>Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($macchinari as $macchinario): 
                                                // Calcolo età in anni
                                                $dataAcquisto = new DateTime($macchinario['data_acquisto']);
                                                $oggi = new DateTime();
                                                $eta = $dataAcquisto->diff($oggi)->y;
                                                
                                                // Stato basato sull'età
                                                $statoClass = '';
                                                $statoText = '';
                                                
                                                if ($eta < 2) {
                                                    $statoClass = 'success';
                                                    $statoText = 'Nuovo';
                                                } else if ($eta < 5) {
                                                    $statoClass = 'info';
                                                    $statoText = 'Buono';
                                                } else if ($eta < 8) {
                                                    $statoClass = 'warning';
                                                    $statoText = 'Verificare';
                                                } else {
                                                    $statoClass = 'danger';
                                                    $statoText = 'Da sostituire';
                                                }
                                            ?>
                                                <tr>
                                                    <td><?= $macchinario['id'] ?></td>
                                                    <td><?= htmlspecialchars($macchinario['matricola']) ?></td>
                                                    <td><?= htmlspecialchars($macchinario['tipologia']) ?></td>
                                                    <td><?= htmlspecialchars($macchinario['produttore']) ?></td>
                                                    <td><?= htmlspecialchars($macchinario['modello']) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($macchinario['data_acquisto'])) ?></td>
                                                    <td><span class="badge badge-<?= $statoClass ?>"><?= $statoText ?> (<?= $eta ?> anni)</span></td>
                                                    <td class="text-center">
                                                        
                                                            <a href="dettaglio_macchinario?id=<?= $macchinario['id'] ?>" class="btn btn-circle btn-light text-info " title="Visualizza">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="edit_macchinario?id=<?= $macchinario['id'] ?>" class="btn btn-circle btn-light text-primary" title="Modifica">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-circle btn-light text-danger btn-delete" data-id="<?= $macchinario['id'] ?>" data-matricola="<?= htmlspecialchars($macchinario['matricola']) ?>" title="Elimina">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                           
                                                        
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Paginazione -->
                                <?php if ($totalPages > 1): ?>
                                <nav aria-label="Navigazione pagine">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>&tipologia=<?= urlencode($tipologia) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                                                <i class="fas fa-angle-double-left"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&tipologia=<?= urlencode($tipologia) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                                                <i class="fas fa-angle-left"></i>
                                            </a>
                                        </li>
                                        <?php else: ?>
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#"><i class="fas fa-angle-double-left"></i></a>
                                        </li>
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#"><i class="fas fa-angle-left"></i></a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php
                                        $startPage = max(1, $page - 2);
                                        $endPage = min($totalPages, $page + 2);
                                        
                                        if ($startPage > 1) {
                                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                        }
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&tipologia=<?= urlencode($tipologia) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($endPage < $totalPages): ?>
                                        <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                                        <?php endif; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&tipologia=<?= urlencode($tipologia) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                                                <i class="fas fa-angle-right"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&tipologia=<?= urlencode($tipologia) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                                                <i class="fas fa-angle-double-right"></i>
                                            </a>
                                        </li>
                                        <?php else: ?>
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#"><i class="fas fa-angle-right"></i></a>
                                        </li>
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#"><i class="fas fa-angle-double-right"></i></a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                                <?php endif; ?>
                                
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i> Nessun macchinario trovato<?= (!empty($search) || !empty($tipologia)) ? ' con i filtri applicati.' : '.' ?>
                                    <?php if (!empty($search) || !empty($tipologia)): ?>
                                    <a href="lista_macchinari" class="alert-link">Rimuovi i filtri</a> per visualizzare tutti i macchinari.
                                    <?php else: ?>
                                    <a href="new_macchinario" class="alert-link">Aggiungi un nuovo macchinario</a> per iniziare.
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal di conferma eliminazione -->
            <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Conferma Eliminazione</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Sei sicuro di voler eliminare il macchinario con matricola <strong id="delete-matricola"></strong>?
                            <p class="text-danger mt-2"><i class="fas fa-exclamation-triangle"></i> Questa operazione non può essere annullata.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                            <a href="#" id="confirm-delete" class="btn btn-danger">Elimina</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include_once BASE_PATH . '/components/scripts.php'; ?>
            
            <script>
                $(document).ready(function() {
                    // Gestione del modal di eliminazione
                    $('.btn-delete').click(function() {
                        var id = $(this).data('id');
                        var matricola = $(this).data('matricola');
                        $('#delete-matricola').text(matricola);
                        $('#confirm-delete').attr('href', 'lista_macchinari?delete=' + id + '&token=<?= $_SESSION['csrf_token'] ?>');
                        
                        // Usa JavaScript nativo per visualizzare il modal se Bootstrap JS non è disponibile
                        var modal = document.getElementById('deleteModal');
                        if (typeof(bootstrap) !== 'undefined') {
                            var deleteModal = new bootstrap.Modal(modal);
                            deleteModal.show();
                        } else if (typeof($("#deleteModal").modal) === 'function') {
                            $('#deleteModal').modal('show');
                        } else {
                            // Fallback se Bootstrap non è disponibile
                            modal.style.display = 'block';
                            modal.classList.add('show');
                            document.body.classList.add('modal-open');
                            
                            // Crea un backdrop
                            var backdrop = document.createElement('div');
                            backdrop.className = 'modal-backdrop fade show';
                            document.body.appendChild(backdrop);
                        }
                    });
                    
                    // Chiusura modale senza Bootstrap
                    $('.close, .btn-secondary').click(function() {
                        var modal = document.getElementById('deleteModal');
                        modal.style.display = 'none';
                        modal.classList.remove('show');
                        document.body.classList.remove('modal-open');
                        
                        var backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.parentNode.removeChild(backdrop);
                        }
                    });
                    
                    // Gestione degli eventi per i pulsanti di esportazione (da implementare)
                    $('#esportaExcel').click(function(e) {
                        e.preventDefault();
                        alert('Funzionalità di esportazione in Excel da implementare.');
                    });
                    
                    $('#esportaPdf').click(function(e) {
                        e.preventDefault();
                        alert('Funzionalità di esportazione in PDF da implementare.');
                    });
                });
                
                // Funzioni per le operazioni avanzate
                function stampaDatiMacchinario(id) {
                    alert('Funzionalità di stampa da implementare per ID: ' + id);
                }
                
                function duplicaMacchinario(id) {
                    alert('Funzionalità di duplicazione da implementare per ID: ' + id);
                }
            </script>
            
            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>
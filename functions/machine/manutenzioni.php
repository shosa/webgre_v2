<?php
session_start();
require_once '../../config/config.php';
require_once BASE_PATH . '/components/auth_validate.php';
require_once '../../utils/log_utils.php';

// Get database instance
$pdo = getDbInstance();

// Process operations (approve, reject, delete)
$operationMessage = '';
$operationType = '';

// Approve maintenance
if (isset($_GET['approve']) && !empty($_GET['approve'])) {
    try {
        $id = filter_var($_GET['approve'], FILTER_VALIDATE_INT);

        $stmt = $pdo->prepare("UPDATE mac_manutenzioni SET 
            stato = 'approvata', 
            approvata_da = ?, 
            data_approvazione = NOW() 
            WHERE id = ?");

        $result = $stmt->execute([
            $_SESSION['user_name'] ?? 'Admin',
            $id
        ]);

        if ($result) {
            $operationMessage = "Manutenzione approvata con successo.";
            $operationType = "success";
        } else {
            $operationMessage = "Errore durante l'approvazione.";
            $operationType = "danger";
        }
    } catch (PDOException $e) {
        $operationMessage = "Errore durante l'approvazione: " . $e->getMessage();
        $operationType = "danger";
    }
}

// Reject maintenance
if (isset($_POST['action']) && $_POST['action'] === 'reject') {
    try {
        $id = filter_var($_POST['maintenance_id'], FILTER_VALIDATE_INT);

        $stmt = $pdo->prepare("UPDATE mac_manutenzioni SET 
            stato = 'rifiutata', 
            approvata_da = ?, 
            note_approvazione = ?,
            data_approvazione = NOW() 
            WHERE id = ?");

        $result = $stmt->execute([
            $_SESSION['user_name'] ?? 'Admin',
            $_POST['note_rifiuto'],
            $id
        ]);

        if ($result) {
            $operationMessage = "Manutenzione rifiutata con successo.";
            $operationType = "warning";
        } else {
            $operationMessage = "Errore durante il rifiuto.";
            $operationType = "danger";
        }
    } catch (PDOException $e) {
        $operationMessage = "Errore durante l'operazione: " . $e->getMessage();
        $operationType = "danger";
    }
}

// Delete maintenance
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    try {
        $id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);

        // Begin transaction
        $pdo->beginTransaction();

        // Delete attachments first (to avoid orphaned files)
        $stmt = $pdo->prepare("SELECT percorso_file FROM mac_manutenzioni_allegati WHERE manutenzione_id = ?");
        $stmt->execute([$id]);
        $attachments = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($attachments as $filepath) {
            $fullPath = BASE_PATH . '/' . $filepath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        // Delete attachments records
        $stmt = $pdo->prepare("DELETE FROM mac_manutenzioni_allegati WHERE manutenzione_id = ?");
        $stmt->execute([$id]);

        // Delete maintenance record
        $stmt = $pdo->prepare("DELETE FROM mac_manutenzioni WHERE id = ?");
        $result = $stmt->execute([$id]);

        $pdo->commit();

        if ($result) {
            $operationMessage = "Manutenzione eliminata con successo.";
            $operationType = "success";
        } else {
            $operationMessage = "Errore durante l'eliminazione.";
            $operationType = "danger";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $operationMessage = "Errore durante l'eliminazione: " . $e->getMessage();
        $operationType = "danger";
    }
}

// Set filters and sorting
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$mac_id = isset($_GET['mac_id']) ? filter_var($_GET['mac_id'], FILTER_VALIDATE_INT) : null;
$tipo_id = isset($_GET['tipo_id']) ? filter_var($_GET['tipo_id'], FILTER_VALIDATE_INT) : null;
$stato = isset($_GET['stato']) ? $_GET['stato'] : '';
$data_da = isset($_GET['data_da']) ? $_GET['data_da'] : '';
$data_a = isset($_GET['data_a']) ? $_GET['data_a'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'data_manutenzione';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$itemsPerPage = 10;

// Base query
$queryCount = "SELECT COUNT(*) FROM mac_manutenzioni m 
                JOIN mac_anag a ON m.mac_id = a.id 
                JOIN mac_manutenzioni_tipi t ON m.tipo_id = t.id 
                WHERE 1=1";

$query = "SELECT m.*, a.matricola, a.tipologia, a.modello, t.nome as tipo_nome, t.colore as tipo_colore 
          FROM mac_manutenzioni m 
          JOIN mac_anag a ON m.mac_id = a.id 
          JOIN mac_manutenzioni_tipi t ON m.tipo_id = t.id 
          WHERE 1=1";

// Add filters
$params = [];

if (!empty($search)) {
    $query .= " AND (a.matricola LIKE ? OR a.modello LIKE ? OR m.operatore LIKE ? OR m.descrizione LIKE ?)";
    $queryCount .= " AND (a.matricola LIKE ? OR a.modello LIKE ? OR m.operatore LIKE ? OR m.descrizione LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

if (!empty($mac_id)) {
    $query .= " AND m.mac_id = ?";
    $queryCount .= " AND m.mac_id = ?";
    $params[] = $mac_id;
}

if (!empty($tipo_id)) {
    $query .= " AND m.tipo_id = ?";
    $queryCount .= " AND m.tipo_id = ?";
    $params[] = $tipo_id;
}

if (!empty($stato)) {
    $query .= " AND m.stato = ?";
    $queryCount .= " AND m.stato = ?";
    $params[] = $stato;
}

if (!empty($data_da)) {
    $query .= " AND m.data_manutenzione >= ?";
    $queryCount .= " AND m.data_manutenzione >= ?";
    $params[] = $data_da;
}

if (!empty($data_a)) {
    $query .= " AND m.data_manutenzione <= ?";
    $queryCount .= " AND m.data_manutenzione <= ?";
    $params[] = $data_a;
}

// Ordering
$validSortColumns = ['data_manutenzione', 'stato', 'operatore', 'data_creazione'];
$validSortOrders = ['ASC', 'DESC'];

if (!in_array($sort, $validSortColumns)) {
    $sort = 'data_manutenzione';
}

if (!in_array($order, $validSortOrders)) {
    $order = 'DESC';
}

$query .= " ORDER BY $sort $order";

// Pagination
$stmtCount = $pdo->prepare($queryCount);
$stmtCount->execute($params);
$totalItems = $stmtCount->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);
$page = min($page, max(1, $totalPages));
$offset = ($page - 1) * $itemsPerPage;

$query .= " LIMIT $offset, $itemsPerPage";

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$manutenzioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get maintenance types for filter
$stmtTipi = $pdo->query("SELECT id, nome FROM mac_manutenzioni_tipi ORDER BY nome");
$tipi_manutenzione = $stmtTipi->fetchAll(PDO::FETCH_ASSOC);

// Get machine info if filtering by machine
$macchinaInfo = null;
if ($mac_id) {
    $stmtMacchina = $pdo->prepare("SELECT * FROM mac_anag WHERE id = ?");
    $stmtMacchina->execute([$mac_id]);
    $macchinaInfo = $stmtMacchina->fetch(PDO::FETCH_ASSOC);
}

// Include header
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
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-tools mr-2"></i>
                            <?= $macchinaInfo ? "Manutenzioni: " . htmlspecialchars($macchinaInfo['matricola']) : "Gestione Manutenzioni" ?>
                        </h1>
                        <div>
                            <?php if ($mac_id): ?>
                                <a href="manutenzioni?mac_id=<?= $mac_id ?>&action=new"
                                    class="btn btn-success btn-sm shadow-sm">
                                    <i class="fas fa-plus fa-sm text-white-50"></i> Nuova Manutenzione
                                </a>
                                <a href="dettaglio_macchinario?id=<?= $mac_id ?>"
                                    class="btn btn-info btn-sm shadow-sm ml-2">
                                    <i class="fas fa-clipboard-list fa-sm text-white-50"></i> Scheda Macchinario
                                </a>
                            <?php else: ?>
                                <a href="manutenzioni?action=new" class="btn btn-success btn-sm shadow-sm">
                                    <i class="fas fa-plus fa-sm text-white-50"></i> Nuova Manutenzione
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($search) || !empty($tipo_id) || !empty($stato) || !empty($data_da) || !empty($data_a)): ?>
                                <a href="<?= $mac_id ? "manutenzioni?mac_id=$mac_id" : "manutenzioni" ?>"
                                    class="btn btn-secondary btn-sm shadow-sm ml-2">
                                    <i class="fas fa-undo fa-sm text-white-50"></i> Reset Filtri
                                </a>
                            <?php endif; ?>
                            <a href="#" id="exportExcel" class="btn btn-primary btn-sm shadow-sm ml-2">
                                <i class="fas fa-file-excel fa-sm text-white-50"></i> Esporta Excel
                            </a>
                            <a href="#" id="exportPdf" class="btn btn-danger btn-sm shadow-sm ml-2">
                                <i class="fas fa-file-pdf fa-sm text-white-50"></i> Esporta PDF
                            </a>
                        </div>
                    </div>

                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="../../index">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="home">Macchinari</a></li>
                        <?php if ($mac_id): ?>
                            <li class="breadcrumb-item"><a href="dettaglio_macchinario?id=<?= $mac_id ?>">Dettaglio
                                    Macchinario</a></li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active">Manutenzioni</li>
                    </ol>

                    <?php if ($macchinaInfo): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Informazioni Macchinario</h6>
                                <span class="badge badge-secondary px-3 py-2">ID: <?= $mac_id ?></span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Matricola
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= htmlspecialchars($macchinaInfo['matricola']) ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Tipologia
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= htmlspecialchars($macchinaInfo['tipologia']) ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Fornitore
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= htmlspecialchars($macchinaInfo['fornitore']) ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Modello
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= htmlspecialchars($macchinaInfo['modello']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Filters Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Filtri di Ricerca</h6>
                            <button class="btn btn-link btn-sm" type="button" data-toggle="collapse"
                                data-target="#collapseFilters">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="card-body collapse show" id="collapseFilters">
                            <form method="GET" action="" class="form-inline">
                                <?php if ($mac_id): ?>
                                    <input type="hidden" name="mac_id" value="<?= $mac_id ?>">
                                <?php endif; ?>

                                <div class="form-group mb-2 mr-2">
                                    <label for="search" class="sr-only">Ricerca</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="search" name="search"
                                            placeholder="Matricola, Operatore, Descrizione..."
                                            value="<?= htmlspecialchars($search) ?>">
                                    </div>
                                </div>

                                <div class="form-group mb-2 mr-2">
                                    <label for="data_da" class="sr-only">Data Da</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Da</span>
                                        </div>
                                        <input type="date" class="form-control" id="data_da" name="data_da"
                                            value="<?= htmlspecialchars($data_da) ?>">
                                    </div>
                                </div>

                                <div class="form-group mb-2 mr-2">
                                    <label for="data_a" class="sr-only">Data A</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">A</span>
                                        </div>
                                        <input type="date" class="form-control" id="data_a" name="data_a"
                                            value="<?= htmlspecialchars($data_a) ?>">
                                    </div>
                                </div>

                                <div class="form-group mb-2 mr-2">
                                    <label for="sort" class="sr-only">Ordinamento</label>
                                    <select class="form-control" id="sort" name="sort">
                                        <option value="data_manutenzione" <?= ($sort == 'data_manutenzione') ? 'selected' : '' ?>>Data Manutenzione</option>
                                        <option value="stato" <?= ($sort == 'stato') ? 'selected' : '' ?>>Stato</option>
                                        <option value="operatore" <?= ($sort == 'operatore') ? 'selected' : '' ?>>Operatore
                                        </option>
                                        <option value="data_creazione" <?= ($sort == 'data_creazione') ? 'selected' : '' ?>>Data Registrazione</option>
                                    </select>
                                </div>

                                <div class="form-group mb-2 mr-2">
                                    <label for="order" class="sr-only">Direzione</label>
                                    <select class="form-control" id="order" name="order">
                                        <option value="DESC" <?= ($order == 'DESC') ? 'selected' : '' ?>>Decrescente
                                        </option>
                                        <option value="ASC" <?= ($order == 'ASC') ? 'selected' : '' ?>>Crescente</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary mb-2">Applica Filtri</button>
                            </form>
                        </div>
                    </div>

                    <!-- Maintenance List -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Elenco Manutenzioni
                                <span class="badge badge-secondary"><?= $totalItems ?> risultati</span>
                                <?php if (!empty($search)): ?>
                                    <span class="badge badge-info">Ricerca: <?= htmlspecialchars($search) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($tipo_id)):
                                    $tipoNome = '';
                                    foreach ($tipi_manutenzione as $tipo) {
                                        if ($tipo['id'] == $tipo_id) {
                                            $tipoNome = $tipo['nome'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <span class="badge badge-info">Tipo: <?= htmlspecialchars($tipoNome) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($stato)): ?>
                                    <span class="badge badge-info">Stato:
                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $stato))) ?></span>
                                <?php endif; ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (count($manutenzioni) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="manutenzioni-table" width="100%"
                                        cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <?php if (!$mac_id): ?>
                                                    <th>Macchinario</th>
                                                <?php endif; ?>
                                                <th>Tipo</th>
                                                <th>Data</th>
                                                <th>Operatore</th>
                                                <th>Descrizione</th>
                                                <th>Stato</th>
                                                <th>Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($manutenzioni as $manutenzione): ?>
                                                <tr>
                                                    <td><?= $manutenzione['id'] ?></td>
                                                    <?php if (!$mac_id): ?>
                                                        <td>
                                                            <a href="dettaglio_macchinario?id=<?= $manutenzione['mac_id'] ?>">
                                                                <?= htmlspecialchars($manutenzione['matricola']) ?>
                                                            </a>
                                                            <div class="small text-muted">
                                                                <?= htmlspecialchars($manutenzione['modello']) ?>
                                                            </div>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td>
                                                        <span class="badge badge-pill"
                                                            style="background-color: <?= htmlspecialchars($manutenzione['tipo_colore']) ?>; color: white;">
                                                            <?= htmlspecialchars($manutenzione['tipo_nome']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($manutenzione['data_manutenzione'])) ?></td>
                                                    <td><?= htmlspecialchars($manutenzione['operatore']) ?></td>
                                                    <td>
                                                        <?= htmlspecialchars(mb_substr($manutenzione['descrizione'], 0, 50)) ?>
                                                        <?= (mb_strlen($manutenzione['descrizione']) > 50) ? '...' : '' ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $statusLabels = [
                                                            'richiesta' => '<span class="badge badge-warning">In Attesa</span>',
                                                            'in_corso' => '<span class="badge badge-info">In Corso</span>',
                                                            'completata' => '<span class="badge badge-primary">Completata</span>',
                                                            'approvata' => '<span class="badge badge-success">Approvata</span>',
                                                            'rifiutata' => '<span class="badge badge-danger">Rifiutata</span>'
                                                        ];
                                                        echo $statusLabels[$manutenzione['stato']] ?? '<span class="badge badge-secondary">Sconosciuto</span>';
                                                        ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="dettaglio_manutenzione?id=<?= $manutenzione['id'] ?>"
                                                            class="btn btn-sm btn-info" title="Dettagli">
                                                            <i class="fas fa-eye"></i>
                                                        </a>

                                                        <?php if ($manutenzione['stato'] == 'richiesta' || $manutenzione['stato'] == 'completata'): ?>
                                                            <a href="?<?= http_build_query(array_merge($_GET, ['approve' => $manutenzione['id']])) ?>"
                                                                class="btn btn-sm btn-success" title="Approva">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-warning reject-btn"
                                                                data-id="<?= $manutenzione['id'] ?>"
                                                                data-operatore="<?= htmlspecialchars($manutenzione['operatore']) ?>"
                                                                title="Rifiuta">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php endif; ?>

                                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                            data-id="<?= $manutenzione['id'] ?>"
                                                            data-data="<?= date('d/m/Y', strtotime($manutenzione['data_manutenzione'])) ?>"
                                                            title="Elimina">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                    <nav aria-label="Navigazione pagine">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">
                                                        <i class="fas fa-angle-double-left"></i>
                                                    </a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
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
                                                    <a class="page-link"
                                                        href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                                        <?= $i ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($endPage < $totalPages): ?>
                                                <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                                            <?php endif; ?>

                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                                        <i class="fas fa-angle-right"></i>
                                                    </a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>">
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
                                    <i class="fas fa-info-circle mr-2"></i> Nessuna manutenzione
                                    trovata<?= (!empty($search) || !empty($tipo_id) || !empty($stato) || !empty($data_da) || !empty($data_a)) ? ' con i filtri applicati.' : '.' ?>
                                    <?php if (!empty($search) || !empty($tipo_id) || !empty($stato) || !empty($data_da) || !empty($data_a)): ?>
                                        <a href="<?= $mac_id ? "manutenzioni?mac_id=$mac_id" : "manutenzioni" ?>"
                                            class="alert-link">Rimuovi i filtri</a> per visualizzare tutte le manutenzioni.
                                    <?php else: ?>
                                        <a href="<?= $mac_id ? "manutenzioni?mac_id=$mac_id&action=new" : "manutenzioni?action=new" ?>"
                                            class="alert-link">Registra una nuova manutenzione</a> per iniziare.
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reject Modal -->
            <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Rifiuta Manutenzione</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="maintenance_id" id="reject_id">
                            <div class="modal-body">
                                <p>Stai per rifiutare la manutenzione eseguita da <strong
                                        id="reject_operatore"></strong>.</p>
                                <div class="form-group">
                                    <label for="note_rifiuto"><strong>Motivo del rifiuto</strong></label>
                                    <textarea name="note_rifiuto" id="note_rifiuto" class="form-control" rows="3"
                                        required placeholder="Inserisci il motivo del rifiuto..."></textarea>
                                </div>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Il tecnico verrà informato del rifiuto e del motivo.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                                <button type="submit" class="btn btn-warning">Rifiuta Manutenzione</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Conferma Eliminazione</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Sei sicuro di voler eliminare la manutenzione del <strong id="delete_data"></strong>?</p>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Attenzione:</strong> Questa operazione non può essere annullata e tutti gli
                                allegati associati verranno eliminati.
                            </div>
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
                $(document).ready(function () {
                    // Funzione helper per mostrare un modal
                    function showModal(modalId) {
                        var modal = document.getElementById(modalId);
                        if (modal) {
                            modal.style.display = 'block';
                            modal.classList.add('show');
                            document.body.classList.add('modal-open');

                            // Crea un backdrop
                            var backdrop = document.createElement('div');
                            backdrop.className = 'modal-backdrop fade show';
                            document.body.appendChild(backdrop);
                        }
                    }

                    // Funzione helper per nascondere un modal
                    function hideModal(modalId) {
                        var modal = document.getElementById(modalId);
                        if (modal) {
                            modal.style.display = 'none';
                            modal.classList.remove('show');
                            document.body.classList.remove('modal-open');

                            // Rimuovi il backdrop
                            var backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) {
                                backdrop.parentNode.removeChild(backdrop);
                            }
                        }
                    }

                    // Reject button click
                    $('.reject-btn').click(function () {
                        $('#reject_id').val($(this).data('id'));
                        $('#reject_operatore').text($(this).data('operatore'));
                        showModal('rejectModal');
                    });

                    // Delete button click
                    $('.delete-btn').click(function () {
                        $('#delete_data').text($(this).data('data'));
                        $('#confirm-delete').attr('href', '?<?= http_build_query(array_merge($_GET, ['delete' => ''])) ?>' + $(this).data('id'));
                        showModal('deleteModal');
                    });

                    // Chiudi i modal quando si clicca su Annulla o sulla X
                    $('.btn-secondary, .close').click(function () {
                        var modalId = $(this).closest('.modal').attr('id');
                        hideModal(modalId);
                    });

                    // Inizializza DataTable se disponibile
                    if (typeof $.fn.DataTable !== 'undefined') {
                        $('#manutenzioni-table').DataTable({
                            "paging": false,
                            "ordering": false,
                            "info": false,
                            "searching": false,
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Italian.json'
                            }
                        });
                    }

                    // Export to Excel
                    $('#exportExcel').click(function (e) {
                        e.preventDefault();
                        // Build current URL with export parameter
                        var currentUrl = window.location.href;
                        var exportUrl = currentUrl + (currentUrl.indexOf('?') > -1 ? '&' : '?') + 'export=excel';
                        window.location.href = exportUrl;
                    });

                    // Export to PDF
                    $('#exportPdf').click(function (e) {
                        e.preventDefault();
                        // Build current URL with export parameter
                        var currentUrl = window.location.href;
                        var exportUrl = currentUrl + (currentUrl.indexOf('?') > -1 ? '&' : '?') + 'export=pdf';
                        window.location.href = exportUrl;
                    });
                });
            </script>

            <?php include_once BASE_PATH . '/components/footer.php'; ?>
        </div>
    </div>
</body>